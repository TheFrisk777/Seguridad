'use strict';
const carrito = document.getElementById('carrito');
const elementos1 = document.getElementById('lista-1');
const lista = document.querySelector('#lista-carrito tbody');
const vaciarCarritoBtn = document.getElementById('vaciar-carrito');
if (elementos1 && carrito && lista && vaciarCarritoBtn) {
    elementos1.addEventListener('click', e => {
        const buy = e.target.closest('.agregar-carrito');
        if (!buy) return;
        e.preventDefault();
        const item = buy.closest('.product');
        if (!item) return;
        const image = item.querySelector('img');
        const title = item.querySelector('h3');
        const price = item.querySelector('.precio');
        if (!image || !title || !price) return;
        const url = new URL(image.src, location.href);
        if (url.origin !== location.origin || !['http:', 'https:'].includes(url.protocol)) return;
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        const img = document.createElement('img');
        img.src = url.href; img.width = 100; img.alt = title.textContent;
        cell.append(img); row.append(cell);
        for (const text of [title.textContent, price.textContent]) {
            const td = document.createElement('td');td.textContent = text;row.append(td);
        }
        const td = document.createElement('td');
        const remove = document.createElement('button');remove.type='button';remove.className='borrar';remove.textContent='X';
        td.append(remove);row.append(td);lista.append(row);
    });
    carrito.addEventListener('click', e => { if (e.target.closest('.borrar')) {e.preventDefault();e.target.closest('tr').remove();} });
    vaciarCarritoBtn.addEventListener('click', e => {e.preventDefault();lista.replaceChildren();});
}
