/**
 * NovaFlow Fastify - In-Memory High-Speed Cache & Data Store
 */

export class MemoryCache {
  private static store = new Map<string, { value: any; expiry: number | null }>();

  static set(key: string, value: any, ttlSeconds: number | null = null): void {
    const expiry = ttlSeconds ? Date.now() + ttlSeconds * 1000 : null;
    this.store.set(key, { value, expiry });
  }

  static get<T = any>(key: string): T | null {
    const item = this.store.get(key);
    if (!item) return null;
    if (item.expiry && Date.now() > item.expiry) {
      this.store.delete(key);
      return null;
    }
    return item.value as T;
  }

  static has(key: string): boolean {
    return this.get(key) !== null;
  }

  static delete(key: string): boolean {
    return this.store.delete(key);
  }

  static clear(): void {
    this.store.clear();
  }
}

export interface UserRecord {
  id: number;
  name: string;
  email: string;
  passwordHash: string;
  role: 'admin' | 'manager' | 'user';
  status: 'active' | 'suspended';
  createdAt: string;
}

export const initialUsers: UserRecord[] = [
  {
    id: 1,
    name: 'Tanvir Hasan',
    email: 'tanvir@example.com',
    passwordHash: 'password123',
    role: 'admin',
    status: 'active',
    createdAt: '2026-01-15 10:24:00'
  },
  {
    id: 2,
    name: 'Shohagh Mia',
    email: 'shohaghInfo@gmail.com',
    passwordHash: 'password123',
    role: 'admin',
    status: 'active',
    createdAt: '2026-02-01 14:12:00'
  },
  {
    id: 3,
    name: 'Farhana Akter',
    email: 'farhana@novaflow.test',
    passwordHash: 'password123',
    role: 'manager',
    status: 'active',
    createdAt: '2026-03-10 09:30:00'
  },
  {
    id: 4,
    name: 'Kamrul Islam',
    email: 'kamrul@novaflow.test',
    passwordHash: 'password123',
    role: 'user',
    status: 'suspended',
    createdAt: '2026-03-22 18:45:00'
  }
];

export class UserStore {
  private static users: UserRecord[] = [...initialUsers];

  static all(): UserRecord[] {
    return [...this.users];
  }

  static findById(id: number): UserRecord | undefined {
    return this.users.find(u => u.id === id);
  }

  static findByEmail(email: string): UserRecord | undefined {
    return this.users.find(u => u.email.toLowerCase() === email.toLowerCase());
  }

  static create(user: Omit<UserRecord, 'id' | 'createdAt'>): UserRecord {
    const newRecord: UserRecord = {
      ...user,
      id: Date.now(),
      createdAt: new Date().toISOString().slice(0, 19).replace('T', ' ')
    };
    this.users.unshift(newRecord);
    return newRecord;
  }

  static update(id: number, fields: Partial<UserRecord>): UserRecord | null {
    const idx = this.users.findIndex(u => u.id === id);
    if (idx === -1) return null;
    this.users[idx] = { ...this.users[idx], ...fields };
    return this.users[idx];
  }

  static delete(id: number): boolean {
    const len = this.users.length;
    this.users = this.users.filter(u => u.id !== id);
    return this.users.length < len;
  }

  static toggleStatus(id: number): UserRecord | null {
    const user = this.findById(id);
    if (!user) return null;
    user.status = user.status === 'active' ? 'suspended' : 'active';
    return user;
  }
}
