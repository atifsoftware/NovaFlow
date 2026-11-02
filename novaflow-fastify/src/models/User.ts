import { BaseModel } from '../core/model';
import { initialUsers, UserRecord } from '../core/store';

export class User extends BaseModel {
  public static table = 'users';

  // Seed default users into model store
  static {
    for (const u of initialUsers) {
      this.getStore().set(u.id, u);
    }
  }
}
