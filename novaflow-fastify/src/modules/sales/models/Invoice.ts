import { BaseModel } from '../../../core/model';

export class Invoice extends BaseModel {
  public static table = 'invoices';

  static {
    // Seed sample sales invoices
    const sampleInvoices = [
      { id: 101, invoiceNumber: 'INV-2026-001', customer: 'Acme Corporation', amount: 1540.00, status: 'paid', date: '2026-04-10' },
      { id: 102, invoiceNumber: 'INV-2026-002', customer: 'Dhaka Tech Ltd', amount: 3200.50, status: 'pending', date: '2026-04-12' },
      { id: 103, invoiceNumber: 'INV-2026-003', customer: 'Bengal Traders', amount: 890.00, status: 'paid', date: '2026-04-14' }
    ];
    for (const inv of sampleInvoices) {
      this.getStore().set(inv.id, inv);
    }
  }
}
