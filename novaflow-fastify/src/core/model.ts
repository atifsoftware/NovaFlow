/**
 * NovaFlow Fastify - Base Active Record Model
 * Integrates directly with QueryBuilder and In-Memory Data Store
 */

import { QueryBuilder } from './queryBuilder';

export abstract class BaseModel {
  public static table: string = '';
  protected static store: Map<number, any> = new Map();

  public id: number = 0;
  [key: string]: any;

  constructor(attributes: Record<string, any> = {}) {
    Object.assign(this, attributes);
  }

  /**
   * Start a fluent query on this model's table
   */
  public static query(driver: 'mysql' | 'pgsql' | 'sqlite' = 'mysql'): QueryBuilder {
    return QueryBuilder.table(this.table, driver);
  }

  /**
   * Fetch all records
   */
  public static all<T extends BaseModel>(this: new (attrs?: any) => T): T[] {
    const store = (this as any).getStore();
    return Array.from(store.values()).map((row: any) => new this(row));
  }

  /**
   * Find record by ID
   */
  public static find<T extends BaseModel>(this: new (attrs?: any) => T, id: number): T | null {
    const store = (this as any).getStore();
    const row = store.get(id);
    return row ? new this(row) : null;
  }

  /**
   * Filter records with simple where clause
   */
  public static where<T extends BaseModel>(this: new (attrs?: any) => T, column: string, value: any): T[] {
    const store = (this as any).getStore();
    const results: T[] = [];
    for (const row of store.values()) {
      if (row[column] === value) {
        results.push(new this(row));
      }
    }
    return results;
  }

  /**
   * Create and persist new record
   */
  public static create<T extends BaseModel>(this: new (attrs?: any) => T, attributes: Record<string, any>): T {
    const store = (this as any).getStore();
    const id = attributes.id || Date.now() + Math.floor(Math.random() * 1000);
    const record = {
      ...attributes,
      id,
      createdAt: new Date().toISOString().slice(0, 19).replace('T', ' ')
    };
    store.set(id, record);
    return new this(record);
  }

  /**
   * Update record by ID
   */
  public static update<T extends BaseModel>(this: new (attrs?: any) => T, id: number, attributes: Record<string, any>): T | null {
    const store = (this as any).getStore();
    const existing = store.get(id);
    if (!existing) return null;
    const updated = { ...existing, ...attributes };
    store.set(id, updated);
    return new this(updated);
  }

  /**
   * Delete record by ID
   */
  public static delete(id: number): boolean {
    const store = (this as any).getStore();
    return store.delete(id);
  }

  protected static getStore(): Map<number, any> {
    if (!this.store) {
      this.store = new Map();
    }
    return this.store;
  }
}
