/**
 * NovaFlow Fastify - Fluent QueryBuilder
 * Ultra-fast query builder with compile caching and in-memory evaluation
 */

import { BaseGrammar, MySqlGrammar, PostgresGrammar, SqliteGrammar, GrammarInterface } from './grammar';

export class QueryBuilder {
  public table: string = '';
  public columns: string | string[] = '*';
  public wheres: any[] = [];
  public joins: string[] = [];
  public orderBy: { column: string; direction: string } | null = null;
  public groupBy: string | null = null;
  public having: string | null = null;
  public limit: number | null = null;
  public offset: number | null = null;

  protected grammar: GrammarInterface;

  constructor(grammar?: GrammarInterface) {
    this.grammar = grammar || new MySqlGrammar();
  }

  static table(table: string, driver: 'mysql' | 'pgsql' | 'sqlite' = 'mysql'): QueryBuilder {
    const grammars = {
      mysql: new MySqlGrammar(),
      pgsql: new PostgresGrammar(),
      sqlite: new SqliteGrammar()
    };
    const qb = new QueryBuilder(grammars[driver]);
    qb.table = table;
    return qb;
  }

  select(columns: string | string[]): this {
    this.columns = columns;
    return this;
  }

  where(column: string, operator: string, value?: any): this {
    if (value === undefined) {
      value = operator;
      operator = '=';
    }
    this.wheres.push({ column, operator, value, boolean: 'AND' });
    return this;
  }

  orWhere(column: string, operator: string, value?: any): this {
    if (value === undefined) {
      value = operator;
      operator = '=';
    }
    this.wheres.push({ column, operator, value, boolean: 'OR' });
    return this;
  }

  whereIn(column: string, values: any[]): this {
    this.wheres.push({ column, operator: 'IN', value: values, boolean: 'AND' });
    return this;
  }

  whereNull(column: string): this {
    this.wheres.push({ column, operator: 'IS NULL', value: null, boolean: 'AND' });
    return this;
  }

  whereNotNull(column: string): this {
    this.wheres.push({ column, operator: 'IS NOT NULL', value: null, boolean: 'AND' });
    return this;
  }

  orderByColumn(column: string, direction: 'ASC' | 'DESC' = 'ASC'): this {
    this.orderBy = { column, direction };
    return this;
  }

  take(limit: number): this {
    this.limit = limit;
    return this;
  }

  skip(offset: number): this {
    this.offset = offset;
    return this;
  }

  toSql(): string {
    return this.grammar.compileSelect(this);
  }

  getBindings(): any[] {
    const bindings: any[] = [];
    for (const w of this.wheres) {
      if (w.operator === 'IN') {
        bindings.push(...w.value);
      } else if (w.value !== null) {
        bindings.push(w.value);
      }
    }
    return bindings;
  }
}
