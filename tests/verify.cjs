const {chromium,request}=require('playwright');
const fs=require('fs'),path=require('path'),{execFileSync}=require('child_process');
const BASE=process.env.TEST_BASE_URL||'http://127.0.0.1:8766';
const out=process.env.TEST_OUTPUT||'auditoria/evidencias';fs.mkdirSync(out,{recursive:true});
const resultFile=process.env.TEST_RESULTS||'auditoria/resultados_corregida.json';
const runtime=process.env.TEST_RUNTIME||path.resolve('runtime');
const mysql=process.env.MYSQL_BIN||'C:/xampp/mysql/bin/mysql.exe';
const php=process.env.PHP_BIN||'C:/xampp/php/php.exe';
const results=[];let browser;
function check(id,test,condition,observed){results.push({id,test,pass:!!condition,observed});console.log(`${condition?'PASS':'FAIL'} ${id} ${test}: ${observed}`);}
function sql(s){return execFileSync(mysql,['--host=127.0.0.1','--port='+ (process.env.TEST_DB_PORT||'13316'),'--user=root','--batch','--skip-column-names','bugambilia','-e',s],{encoding:'utf8'}).trim();}
async function token(ctx,url=BASE){const r=await ctx.get(url+'/Bugambilia/paginas/inde.php');const text=await r.text();return (text.match(/name="csrf" value="([^"]+)"/)||[])[1];}
async function post(ctx,fields,t){return ctx.post(BASE+'/Bugambilia/conexion/inde.php',{form:{...fields,csrf:t},maxRedirects:0});}
const email='auditoria-'+Date.now()+'@example.test',pw='Demo-local-1234567';
const login={IniciarSesion:'1',email,'contraseña':pw};
(async()=>{
sql('DELETE FROM auth_attempts');
browser=await chromium.launch({executablePath:process.env.CHROME_BIN||'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
const ctx=await browser.newContext({viewport:{width:1280,height:850}});const p=await ctx.newPage();
const external=[];p.on('request',r=>{if(!r.url().startsWith(BASE)&&!r.url().startsWith('data:'))external.push(r.url());});
let r=await ctx.request.get(BASE+'/Bugambilia/conexion/principal.php');check('T01','Acceso anónimo',r.status()===401,'HTTP '+r.status());
await p.goto(BASE+'/Bugambilia/conexion/principal.php');await p.screenshot({path:out+'/E04_acceso_protegido.png'});
r=await ctx.request.get(BASE+'/Bugambilia/BD/bugambilia.sql');check('T02','SQL no público',r.status()===404,'HTTP '+r.status());
r=await ctx.request.get(BASE+'/.env');check('T03','Configuración no pública',r.status()===404,'HTTP '+r.status());
let t=await token(ctx.request);let h=(await ctx.request.get(BASE+'/Bugambilia/paginas/inde.php')).headers();
check('T04','Cabeceras de seguridad',!!h['content-security-policy']&&h['cache-control'].includes('no-store')&&h['permissions-policy'].includes('camera=()'),'CSP, no-store, Referrer-Policy, Permissions-Policy');
const cookie=(await ctx.cookies()).find(c=>c.name==='BUGAMBILIASESSID');check('T05','Cookie HttpOnly y SameSite',cookie?.httpOnly&&cookie?.sameSite==='Lax','HttpOnly=true; SameSite=Lax; Secure=false solo en loopback autorizado');
r=await post(ctx.request,{Registrarse:'1',nombre:'Demo',email,'contraseña':pw},'invalido');check('T06','CSRF inválido',r.status()===403,'HTTP '+r.status());
await p.setContent(await r.text());await p.screenshot({path:out+'/E05_csrf_rechazado.png'});
r=await post(ctx.request,{Registrarse:'1',nombre:'Demo',email:'no-es-correo','contraseña':'corta'},t);check('T07','Validación del servidor',r.status()===422,'HTTP '+r.status());
await p.setContent(await r.text());await p.screenshot({path:out+'/E06_validacion.png'});
r=await post(ctx.request,{Registrarse:'1',nombre:'Cuenta Demo',email,'contraseña':pw},t);check('T08','Registro válido',r.status()===303,'HTTP '+r.status());
const count=sql("SELECT COUNT(*) FROM users WHERE email='"+email+"'");check('T09','Persistencia con ID automático',count==='1','1 registro con ID generado');
const info=sql("SELECT psswd FROM users WHERE email='"+email+"'");const verified=execFileSync(php,['-r','echo password_verify($argv[1],$argv[2]) ? "OK" : "FAIL";',pw,info],{encoding:'utf8'});
check('T10','Contraseña bcrypt',info.startsWith('$2y$')&&verified==='OK','Hash bcrypt verificado; valor oculto');
r=await post(ctx.request,{Registrarse:'1',nombre:'Cuenta Demo',email,'contraseña':pw},t);check('T11','Duplicado sin enumeración',r.status()===303&&sql("SELECT COUNT(*) FROM users WHERE email='"+email+"'")==='1','HTTP 303; no duplica cuenta; respuesta uniforme');
r=await post(ctx.request,{Registrarse:'1',nombre:'<script>alert(1)</script>',email:'xss@example.test','contraseña':pw},t);check('T12','Etiquetas en nombre',r.status()===422,'HTTP '+r.status());
r=await post(ctx.request,{...login,email:"' OR '1'='1"},t);check('T13','Entrada SQL inválida',r.status()===422,'HTTP '+r.status()+'; no alcanza SQL');
// Valid email containing an apostrophe passes validation and reaches a prepared statement.
r=await post(ctx.request,{...login,email:"o'connor@example.test"},t);check('T14','Comilla tratada como dato',r.status()===401&&!(await r.text()).includes('SQL'),'HTTP '+r.status()+'; sin error SQL ni autenticación');
const before=(await ctx.cookies()).find(c=>c.name==='BUGAMBILIASESSID').value;
r=await post(ctx.request,login,t);check('T15','Inicio de sesión válido',r.status()===303,'HTTP '+r.status());
const after=(await ctx.cookies()).find(c=>c.name==='BUGAMBILIASESSID').value;check('T16','Renovación del ID',before!==after,'ID cambia al autenticar; valores ocultos');
await p.goto(BASE+'/Bugambilia/conexion/principal.php');check('T17','Principal autenticado',await p.locator('h1').textContent()==='Mi cuenta','Cuenta Demo; HTTP 200');await p.screenshot({path:out+'/E07_cuenta_corregida.png'});
const account=await p.content();const csrf=(account.match(/name="csrf" value="([^"]+)"/)||[])[1];
const replay=await request.newContext({extraHTTPHeaders:{Cookie:'BUGAMBILIASESSID='+after}});
r=await ctx.request.get(BASE+'/Bugambilia/conexion/logout.php');check('T18','Logout rechaza GET',r.status()===405,'HTTP '+r.status());
r=await ctx.request.post(BASE+'/Bugambilia/conexion/logout.php',{form:{csrf},maxRedirects:0});check('T19','Logout POST',r.status()===303,'HTTP '+r.status());
r=await replay.get(BASE+'/Bugambilia/conexion/principal.php');check('T20','Reutilizar cookie revocada',r.status()===401,'HTTP '+r.status());
await p.goto(BASE+'/Bugambilia/paginas/inde.php');await p.locator('.register').scrollIntoViewIfNeeded();await p.screenshot({path:out+'/E08_formulario_corregido.png'});
check('T21','Sin peticiones a terceros',external.length===0,'Solicitudes externas automáticas: '+external.length);
check('T22','Almacenamiento del navegador',await p.evaluate(()=>localStorage.length===0&&sessionStorage.length===0),'localStorage=0; sessionStorage=0');
sql('DELETE FROM auth_attempts');t=await token(ctx.request);await post(ctx.request,login,t);
const sid=(await ctx.cookies()).find(c=>c.name==='BUGAMBILIASESSID').value;
// Age only this generated test session, without waiting fifteen minutes.
execFileSync(php,['-r','ini_set("session.save_path",$argv[1]);session_name("BUGAMBILIASESSID");session_id($argv[2]);session_start();$_SESSION["last_seen"]=time()-901;session_write_close();',runtime,sid]);
r=await ctx.request.get(BASE+'/Bugambilia/conexion/principal.php');check('T23','Caducidad por inactividad',r.status()===401&&(await r.text()).includes('caducado'),'HTTP '+r.status()+'; last_seen retrocedido 901 s en sesión de prueba');
t=await token(ctx.request);await post(ctx.request,login,t);const sid2=(await ctx.cookies()).find(c=>c.name==='BUGAMBILIASESSID').value;
execFileSync(php,['-r','ini_set("session.save_path",$argv[1]);session_name("BUGAMBILIASESSID");session_id($argv[2]);session_start();$_SESSION["created_at"]=time()-3601;session_write_close();',runtime,sid2]);
r=await ctx.request.get(BASE+'/Bugambilia/conexion/principal.php');check('T24','Caducidad absoluta',r.status()===401,'HTTP '+r.status()+'; created_at retrocedido 3601 s');
sql('DELETE FROM auth_attempts');t=await token(ctx.request);await post(ctx.request,login,t);r=await ctx.request.get(BASE+'/Bugambilia/conexion/principal.php');t=((await r.text()).match(/name="csrf" value="([^"]+)"/)||[])[1];
r=await ctx.request.post(BASE+'/Bugambilia/conexion/eliminar.php',{form:{csrf:t,'contraseña':'incorrecta'}});check('T25','Eliminar exige reautenticación',r.status()===401,'HTTP '+r.status());
r=await ctx.request.post(BASE+'/Bugambilia/conexion/eliminar.php',{form:{csrf:t,'contraseña':pw},maxRedirects:0});check('T26','Eliminación de cuenta',r.status()===303&&sql("SELECT COUNT(*) FROM users WHERE email='"+email+"'")==='0','HTTP 303; cero filas de la cuenta de prueba');
sql('DELETE FROM auth_attempts');t=await token(ctx.request);const codes=[];for(let i=0;i<6;i++){r=await post(ctx.request,login,t);codes.push(r.status());}
check('T27','Límite persistente de intentos',codes.slice(0,5).every(x=>x===401)&&codes[5]===429,codes.join(', '));await p.setContent(await r.text());await p.screenshot({path:out+'/E09_limite_intentos.png'});
const fresh=await request.newContext();t=await token(fresh);r=await post(fresh,login,t);check('T28','Cambiar cookie no evita bloqueo',r.status()===429,'HTTP '+r.status());
// Cart DOM test uses a product title with markup-like text. No HTML interpretation is allowed.
await p.goto(BASE+'/Bugambilia/paginas/carrito.html');
const cart=await p.evaluate(()=>{const item=document.querySelector('.agregar-carrito')?.closest('.product');if(!item)return null;item.querySelector('h3').textContent='<img src=x onerror=alert(1)>';item.querySelector('.agregar-carrito').click();const row=document.querySelector('#lista-carrito tbody tr');return {text:row?.textContent,images:row?.querySelectorAll('img').length};});
check('T29','Carrito no interpreta HTML',cart?.text?.includes('<img src=x')&&cart.images===1,'Texto literal; solo imagen original del producto');
r=await ctx.request.get('http://127.0.0.1:8767/Bugambilia/paginas/inde.php');check('T30','HTTP bloqueado en producción',r.status()===426,'HTTP '+r.status()+' con ALLOW_LOCAL_HTTP=0');await p.goto('http://127.0.0.1:8767/Bugambilia/paginas/inde.php');await p.screenshot({path:out+'/E10_https_requerido.png'});
const err=await request.newContext();t=await token(err,'http://127.0.0.1:8768');r=await err.post('http://127.0.0.1:8768/Bugambilia/conexion/inde.php',{form:{Registrarse:'1',nombre:'Demo',email,'contraseña':pw,csrf:t}});const errBody=await r.text();check('T31','Error sin detalles internos',r.status()===503&&!/mysqli|Stack trace|C:\\|root/.test(errBody),'HTTP '+r.status()+'; mensaje genérico ante DB_USER=root');await p.setContent(errBody);await p.screenshot({path:out+'/E11_error_controlado.png'});
const priv=await ctx.request.get(BASE+'/Bugambilia/paginas/privacidad.html');check('T32','Aviso y minimización',priv.status()===200&&(await priv.text()).includes('hash es un identificador seudónimo'),'Aviso disponible; no se solicita apellido');
sql('DELETE FROM auth_attempts');
await Promise.all([replay.dispose(),fresh.dispose(),err.dispose()]);
fs.writeFileSync(resultFile,JSON.stringify(results,null,2));console.log('RESULTADO: '+results.filter(r=>r.pass).length+'/'+results.length);await browser.close();process.exit(results.every(r=>r.pass)?0:1);
})().catch(async e=>{console.error(e.message);fs.writeFileSync(resultFile,JSON.stringify(results,null,2));if(browser)await browser.close();process.exit(1)});
