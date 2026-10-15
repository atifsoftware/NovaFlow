<?php
/**
 * Sample ERP Module View: Sales / Invoices
 * File: app/Modules/Sales/Views/invoices/index.php
 */
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-file-invoice text-danger me-2"></i> Sales Invoices</h2>
            <p class="text-muted mb-0">Module: <code>App\Modules\Sales</code> &bull; View: <code>Sales::invoices.index</code></p>
        </div>
        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
            <i class="fas fa-check-circle me-1"></i> Modular ERP View Active
        </span>
    </div>

    <div class="card bg-dark border-secondary border-opacity-25 rounded-4 p-4 text-white">
        <h5 class="fw-bold mb-3">Invoices List</h5>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>#INV-2026-001</code></td>
                        <td>Acme Corp</td>
                        <td>$1,250.00</td>
                        <td><span class="badge bg-success">Paid</span></td>
                    </tr>
                    <tr>
                        <td><code>#INV-2026-002</code></td>
                        <td>Globex Industries</td>
                        <td>$3,840.00</td>
                        <td><span class="badge bg-warning text-dark">Pending</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
