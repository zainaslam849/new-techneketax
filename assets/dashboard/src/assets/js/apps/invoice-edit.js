function deleteItemRow() {
    let deleteItem = document.querySelectorAll('.delete-item');
    for (let i = 0; i < deleteItem.length; i++) {
        deleteItem[i].addEventListener('click', function() {
            this.parentElement.parentNode.parentNode.parentNode.remove();
            updateTotal();
        });
    }
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.item-table tbody tr').forEach(function(row) {
        let price = parseFloat(row.querySelector('.price').value) || 0;
        let quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        let amount = price * quantity;
        row.querySelector('.amount').textContent = amount;
        row.querySelector('.amount_item').value = amount; // Update the hidden input with the amount
        total += amount;
    });
    document.getElementById('subtotal_input').value = total;
    document.getElementById('subtotal').textContent = total;
    applyDiscount();
}

function applyDiscount() {
    let subtotal = parseFloat(document.getElementById('subtotal_input').value) || 0;
    let discountType = document.getElementById('discount').value;
    let discountAmount = parseFloat(document.getElementById('rated1').value) || 0;
    let finalTotal = subtotal;
    $('#discountAmount').html(discountAmount);
    $('#discountAmountInput').val(discountAmount);
    if (discountType === 'Percent') {
        finalTotal = subtotal - (subtotal * (discountAmount / 100));
        var discountTotalAmount =subtotal * (discountAmount / 100);
    } else if (discountType === 'Flat Amount') {
        finalTotal = subtotal - discountAmount;
        var discountTotalAmount = discountAmount;
    }

    document.getElementById('final_total').value = finalTotal;
    document.getElementById('discountTotalAmountInput').value = discountTotalAmount;
    $('#finalTotal').html(finalTotal);
    $('#discountTotalAmount').html(discountTotalAmount);
}

function toggleDiscountAmount() {
    let discountType = document.getElementById('discount').value;
    let discountAmountDiv = document.querySelector('.discount-amount');

    if (discountType === 'None') {
        discountAmountDiv.style.display = 'none';
    } else {
        discountAmountDiv.style.display = 'block';
    }
    applyDiscount();
}

document.getElementById('discount').addEventListener('change', toggleDiscountAmount);

document.querySelector('.additem').addEventListener('click', function() {
    let getTableElement = document.querySelector('.item-table');
    let currentIndex = getTableElement.rows.length;

    let $html = '<tr class="border-bottom border-bottom-dashed">'+
        '<td class="delete-item-row">'+
        '<ul class="table-controls">'+
        '<li><a href="javascript:void(0);" class="delete-item" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"> <span class="svg-icon svg-icon-3">\n' +
        '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">\n' +
        '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<path d="M5 9C5 8.44772 5.44772 8 6 8H18C18.5523 8 19 8.44772 19 9V18C19 19.6569 17.6569 21 16 21H8C6.34315 21 5 19.6569 5 18V9Z" fill="currentColor"></path>\n' +
        '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<path opacity="0.5" d="M5 5C5 4.44772 5.44772 4 6 4H18C18.5523 4 19 4.44772 19 5V5C19 5.55228 18.5523 6 18 6H6C5.44772 6 5 5.55228 5 5V5Z" fill="currentColor"></path>\n' +
        '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<path opacity="0.5" d="M9 4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V4H9V4Z" fill="currentColor"></path>\n' +
        '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</svg>\n' +
        '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span></a></li>'+
        '</ul>'+
        '</td>'+
        '<td class="pe-7"><input type="text" class="form-control form-control-solid" name="item_des[]" placeholder="Item Description"> <textarea class="form-control form-control-solid" placeholder="Additional Details" name="additional_details[]"></textarea></td>'+
        '<td class="ps-0">'+
        '<input type="number" class="form-control form-control-solid price" placeholder="Price" name="price[]">'+
        ' </td>'+
        '<td class="text-right qty"><input type="number" class="form-control form-control-solid quantity" value="1" placeholder="Quantity" name="quantity[]"></td>'+
        '<td class="pt-8 text-end text-nowrap"><span class="editable-amount"><span class="currency">$</span> <span class="amount">0.00</span></td><input type="hidden" name="amount_item[]" class="amount_item">'+
        '</tr>';

    document.querySelector(".item-table tbody").insertAdjacentHTML('beforeend', $html);
    deleteItemRow();
    addEventListeners();
    updateTotal();
});

function addEventListeners() {
    document.querySelectorAll('.price, .quantity').forEach(function(input) {
        input.addEventListener('input', updateTotal);
    });
}

addEventListeners();
deleteItemRow();
toggleDiscountAmount();
