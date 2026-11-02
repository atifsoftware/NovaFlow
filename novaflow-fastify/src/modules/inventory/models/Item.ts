import { BaseModel } from '../../../core/model';

export class Item extends BaseModel {
  public static table = 'items';

  static {
    const sampleItems = [
      { id: 201, sku: 'SKU-LAP-001', name: 'Dell XPS 15 Developer Edition', stock: 18, price: 1899.00 },
      { id: 202, sku: 'SKU-MNT-002', name: 'Dell UltraSharp 27 4K Monitor', stock: 35, price: 549.99 },
      { id: 203, sku: 'SKU-KEY-003', name: 'Keychron Q1 Pro Mechanical Keyboard', stock: 50, price: 199.00 }
    ];
    for (const item of sampleItems) {
      this.getStore().set(item.id, item);
    }
  }
}
