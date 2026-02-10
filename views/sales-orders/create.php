<?php use App\View; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-circle"></i> Create Sales Order</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/sales-orders" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form id="salesOrderForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer_name" class="form-label">Customer Name *</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="reference" class="form-label">Reference *</label>
                            <input type="text" class="form-control" id="reference" name="reference"
                                   value="SO-<?= date('Ymd') ?>-<?= rand(1000, 9999) ?>" required>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Order Items</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                            <i class="bi bi-plus-circle"></i> Add Item
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="width: 35%;">Product</th>
                                    <th style="width: 15%;">Available</th>
                                    <th style="width: 15%;">Quantity</th>
                                    <th style="width: 15%;">Unit Price (KES)</th>
                                    <th style="width: 15%;">Total</th>
                                    <th style="width: 5%;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <!-- Items will be added dynamically -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Total Revenue:</th>
                                    <th id="grandTotal">KES 0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="/sales-orders" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Create Sales Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Instructions
            </div>
            <div class="card-body">
                <ol class="small">
                    <li>Enter customer name</li>
                    <li>Click "Add Item" to add products</li>
                    <li>Check "Available" column for stock levels</li>
                    <li>Enter quantity (must not exceed available stock)</li>
                    <li>Sale price is auto-filled from product</li>
                    <li>Click "Create Sales Order" to save</li>
                </ol>
                <div class="alert alert-warning small mt-3 mb-0">
                    <i class="bi bi-exclamation-triangle"></i> Orders exceeding stock will be rejected
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Available products with stock info
const products = <?= json_encode(array_map(function($p) {
    return [
        'id' => $p['id'],
        'name' => $p['name'],
        'sku' => $p['sku'],
        'sale_price' => $p['sale_price'],
        'current_stock' => $p['current_stock']
    ];
}, $products)) ?>;

let itemIndex = 0;

// Add item row
document.getElementById('addItemBtn').addEventListener('click', function() {
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    row.id = `item-row-${itemIndex}`;
    row.innerHTML = `
        <td>
            <select class="form-select form-select-sm item-product" name="items[${itemIndex}][product_id]" required>
                <option value="">Select Product</option>
                ${products.map(p => `<option value="${p.id}" data-price="${p.sale_price}" data-stock="${p.current_stock}">${p.name} (${p.sku})</option>`).join('')}
            </select>
        </td>
        <td class="item-stock text-muted">-</td>
        <td>
            <input type="number" class="form-control form-control-sm item-quantity"
                   name="items[${itemIndex}][quantity]" min="1" value="1" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-price"
                   name="items[${itemIndex}][unit_price]" min="0" step="0.01" readonly required>
        </td>
        <td class="item-total">KES 0.00</td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger remove-item" data-index="${itemIndex}">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);

    // Add event listeners for this row
    const productSelect = row.querySelector('.item-product');
    const quantityInput = row.querySelector('.item-quantity');
    const priceInput = row.querySelector('.item-price');
    const stockCell = row.querySelector('.item-stock');

    productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        const stock = selectedOption.getAttribute('data-stock');

        if (price && stock) {
            priceInput.value = parseFloat(price).toFixed(2);
            stockCell.textContent = stock;
            stockCell.className = 'item-stock ' + (parseInt(stock) > 0 ? 'text-success' : 'text-danger');
            quantityInput.max = stock;
            calculateRowTotal(row);
        }
    });

    quantityInput.addEventListener('input', function() {
        const maxStock = productSelect.options[productSelect.selectedIndex]?.getAttribute('data-stock');
        if (maxStock && parseInt(this.value) > parseInt(maxStock)) {
            this.value = maxStock;
            alert(`Only ${maxStock} units available in stock`);
        }
        calculateRowTotal(row);
    });

    priceInput.addEventListener('input', () => calculateRowTotal(row));

    row.querySelector('.remove-item').addEventListener('click', function() {
        row.remove();
        calculateGrandTotal();
    });

    itemIndex++;
});

// Calculate row total
function calculateRowTotal(row) {
    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    const total = quantity * price;
    row.querySelector('.item-total').textContent = `KES ${total.toFixed(2)}`;
    calculateGrandTotal();
}

// Calculate grand total
function calculateGrandTotal() {
    let total = 0;
    document.querySelectorAll('#itemsBody tr').forEach(row => {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        total += quantity * price;
    });
    document.getElementById('grandTotal').textContent = `KES ${total.toFixed(2)}`;
}

// Add first item automatically
document.getElementById('addItemBtn').click();

// Form submission
document.getElementById('salesOrderForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const items = [];

    // Collect and validate items
    let hasError = false;
    document.querySelectorAll('#itemsBody tr').forEach((row, index) => {
        const productSelect = row.querySelector('.item-product');
        const productId = productSelect.value;
        const quantity = parseInt(row.querySelector('.item-quantity').value);
        const unitPrice = parseFloat(row.querySelector('.item-price').value);
        const availableStock = parseInt(productSelect.options[productSelect.selectedIndex]?.getAttribute('data-stock') || 0);

        if (productId && quantity && unitPrice) {
            if (quantity > availableStock) {
                alert(`Quantity exceeds available stock for ${productSelect.options[productSelect.selectedIndex].text}`);
                hasError = true;
                return;
            }

            items.push({
                product_id: parseInt(productId),
                quantity: quantity,
                unit_price: unitPrice
            });
        }
    });

    if (hasError) return;

    if (items.length === 0) {
        alert('Please add at least one item');
        return;
    }

    const data = {
        customer_name: formData.get('customer_name'),
        reference: formData.get('reference'),
        items: items
    };

    try {
        const response = await fetch('/api/sales-orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok && result.success) {
            alert('Sales order created successfully!');
            window.location.href = '/sales-orders';
        } else {
            alert('Error: ' + (result.error || 'Failed to create sales order'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while creating the sales order');
    }
});
</script>
