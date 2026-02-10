<?php use App\View; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-circle"></i> Create Purchase Order</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/purchase-orders" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form id="purchaseOrderForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="supplier_id" class="form-label">Supplier *</label>
                            <select class="form-select" id="supplier_id" name="supplier_id" required>
                                <option value="">Select Supplier</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= $supplier['id'] ?>">
                                        <?= View::e($supplier['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="reference" class="form-label">Reference *</label>
                            <input type="text" class="form-control" id="reference" name="reference"
                                   value="PO-<?= date('Ymd') ?>-<?= rand(1000, 9999) ?>" required>
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
                                    <th style="width: 40%;">Product</th>
                                    <th style="width: 20%;">Quantity</th>
                                    <th style="width: 20%;">Unit Cost (KES)</th>
                                    <th style="width: 15%;">Total</th>
                                    <th style="width: 5%;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <!-- Items will be added dynamically -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total Cost:</th>
                                    <th id="grandTotal">KES 0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="/purchase-orders" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Create Purchase Order
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
                    <li>Select a supplier from the dropdown</li>
                    <li>Click "Add Item" to add products</li>
                    <li>Enter quantity and unit cost for each item</li>
                    <li>The total will be calculated automatically</li>
                    <li>Click "Create Purchase Order" to save</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<script>
// Available products for dropdown
const products = <?= json_encode(array_map(function($p) {
    return ['id' => $p['id'], 'name' => $p['name'], 'sku' => $p['sku'], 'cost_price' => $p['cost_price']];
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
                ${products.map(p => `<option value="${p.id}" data-cost="${p.cost_price}">${p.name} (${p.sku})</option>`).join('')}
            </select>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-quantity"
                   name="items[${itemIndex}][quantity]" min="1" value="1" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-cost"
                   name="items[${itemIndex}][unit_cost]" min="0" step="0.01" required>
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
    const costInput = row.querySelector('.item-cost');

    productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const cost = selectedOption.getAttribute('data-cost');
        if (cost) {
            costInput.value = parseFloat(cost).toFixed(2);
            calculateRowTotal(row);
        }
    });

    quantityInput.addEventListener('input', () => calculateRowTotal(row));
    costInput.addEventListener('input', () => calculateRowTotal(row));

    row.querySelector('.remove-item').addEventListener('click', function() {
        row.remove();
        calculateGrandTotal();
    });

    itemIndex++;
});

// Calculate row total
function calculateRowTotal(row) {
    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
    const cost = parseFloat(row.querySelector('.item-cost').value) || 0;
    const total = quantity * cost;
    row.querySelector('.item-total').textContent = `KES ${total.toFixed(2)}`;
    calculateGrandTotal();
}

// Calculate grand total
function calculateGrandTotal() {
    let total = 0;
    document.querySelectorAll('#itemsBody tr').forEach(row => {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const cost = parseFloat(row.querySelector('.item-cost').value) || 0;
        total += quantity * cost;
    });
    document.getElementById('grandTotal').textContent = `KES ${total.toFixed(2)}`;
}

// Add first item automatically
document.getElementById('addItemBtn').click();

// Form submission
document.getElementById('purchaseOrderForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const items = [];

    // Collect items
    document.querySelectorAll('#itemsBody tr').forEach((row, index) => {
        const productId = row.querySelector('.item-product').value;
        const quantity = row.querySelector('.item-quantity').value;
        const unitCost = row.querySelector('.item-cost').value;

        if (productId && quantity && unitCost) {
            items.push({
                product_id: parseInt(productId),
                quantity: parseInt(quantity),
                unit_cost: parseFloat(unitCost)
            });
        }
    });

    if (items.length === 0) {
        alert('Please add at least one item');
        return;
    }

    const data = {
        supplier_id: parseInt(formData.get('supplier_id')),
        reference: formData.get('reference'),
        items: items
    };

    try {
        const response = await fetch('/api/purchase-orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok && result.success) {
            alert('Purchase order created successfully!');
            window.location.href = '/purchase-orders';
        } else {
            alert('Error: ' + (result.error || 'Failed to create purchase order'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while creating the purchase order');
    }
});
</script>
