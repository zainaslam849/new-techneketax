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

    let $html = '<tr>'+
        '<td class="delete-item-row">'+
        '<ul class="table-controls">'+
        '<li><a href="javascript:void(0);" class="delete-item" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg></a></li>'+
        '</ul>'+
        '</td>'+
        '<td class="description"><input type="text" class="form-control  form-control-sm" name="item_des[]" placeholder="Item Description"> <textarea class="form-control" placeholder="Additional Details" name="additional_details[]"></textarea></td>'+
        '<td class="rate">'+
        '<input type="text" class="form-control  form-control-sm price" placeholder="Price" name="price[]">'+
        ' </td>'+
        '<td class="text-right qty"><input type="text" class="form-control  form-control-sm quantity" placeholder="Quantity" name="quantity[]"></td>'+
        '<td class="text-right amount"><span class="editable-amount"><span class="currency">$</span> <span class="amount">0.00</span></td><input type="hidden" name="amount_item[]" class="amount_item">'+
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
