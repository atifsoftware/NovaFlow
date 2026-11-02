/**
 * NovaFlow Fastify - Multi-Database Dialect Grammars
 * Decouples SQL syntax compilation from database execution
 */

export interface GrammarInterface {
  wrap(value: string): string;
  compileSelect(builder: any): string;
  compileInsert(table: string, values: Record<string, any>): { sql: string; bindings: any[] };
  compileUpdate(table: string, values: Record<string, any>, wheres: any[]): { sql: string; bindings: any[] };
  compileDelete(table: string, wheres: any[]): { sql: string; bindings: any[] };
}

export abstract class BaseGrammar implements GrammarInterface {
  abstract wrapValue(value: string): string;
  abstract getPlaceholder(index: number): string;

  wrap(value: string): string {
    if (value === '*') return '*';
    if (value.includes(' as ') || value.includes(' AS ')) {
      const parts = value.split(/ as /i);
      return `${this.wrap(parts[0].trim())} AS ${this.wrapValue(parts[1].trim())}`;
    }
    if (value.includes('(') || value.includes(')')) {
      // Guard against injection in raw expressions
      if (/[;\n\r]|--|\/\*|\*\/|#/.test(value)) {
        throw new Error(`Potential SQL injection detected: ${value}`);
      }
      return value;
    }
    if (value.includes('.')) {
      return value.split('.').map(part => this.wrapValue(part)).join('.');
    }
    return this.wrapValue(value);
  }

  compileSelect(builder: any): string {
    const table = this.wrap(builder.table);
    const columns = Array.isArray(builder.columns) 
      ? builder.columns.map((c: string) => this.wrap(c)).join(', ') 
      : (builder.columns || '*');

    let sql = `SELECT ${columns} FROM ${table}`;

    if (builder.joins && builder.joins.length > 0) {
      sql += ' ' + builder.joins.join(' ');
    }

    if (builder.wheres && builder.wheres.length > 0) {
      const whereClauses: string[] = [];
      let paramIdx = 1;
      for (const w of builder.wheres) {
        const col = this.wrap(w.column);
        if (w.operator === 'IN') {
          const placeholders = w.value.map(() => this.getPlaceholder(paramIdx++)).join(', ');
          whereClauses.push(`${w.boolean} ${col} IN (${placeholders})`);
        } else if (w.operator === 'IS NULL' || w.operator === 'IS NOT NULL') {
          whereClauses.push(`${w.boolean} ${col} ${w.operator}`);
        } else {
          whereClauses.push(`${w.boolean} ${col} ${w.operator} ${this.getPlaceholder(paramIdx++)}`);
        }
      }
      sql += ' WHERE ' + whereClauses.join(' ').replace(/^(AND|OR)\s+/i, '');
    }

    if (builder.groupBy) {
      sql += ` GROUP BY ${this.wrap(builder.groupBy)}`;
    }

    if (builder.having) {
      sql += ` ${builder.having}`;
    }

    if (builder.orderBy) {
      sql += ` ORDER BY ${this.wrap(builder.orderBy.column)} ${builder.orderBy.direction}`;
    }

    if (builder.limit) {
      sql += ` LIMIT ${builder.limit}`;
    }

    if (builder.offset) {
      sql += ` OFFSET ${builder.offset}`;
    }

    return sql;
  }

  compileInsert(table: string, values: Record<string, any>): { sql: string; bindings: any[] } {
    const cols = Object.keys(values);
    const wrappedCols = cols.map(c => this.wrap(c)).join(', ');
    const bindings = Object.values(values);
    const placeholders = bindings.map((_, i) => this.getPlaceholder(i + 1)).join(', ');

    return {
      sql: `INSERT INTO ${this.wrap(table)} (${wrappedCols}) VALUES (${placeholders})`,
      bindings
    };
  }

  compileUpdate(table: string, values: Record<string, any>, wheres: any[]): { sql: string; bindings: any[] } {
    const cols = Object.keys(values);
    const bindings: any[] = [];
    let pIdx = 1;

    const setClauses = cols.map(c => {
      bindings.push(values[c]);
      return `${this.wrap(c)} = ${this.getPlaceholder(pIdx++)}`;
    }).join(', ');

    let sql = `UPDATE ${this.wrap(table)} SET ${setClauses}`;

    if (wheres && wheres.length > 0) {
      const whereClauses: string[] = [];
      for (const w of wheres) {
        bindings.push(w.value);
        whereClauses.push(`${w.boolean} ${this.wrap(w.column)} ${w.operator} ${this.getPlaceholder(pIdx++)}`);
      }
      sql += ' WHERE ' + whereClauses.join(' ').replace(/^(AND|OR)\s+/i, '');
    }

    return { sql, bindings };
  }

  compileDelete(table: string, wheres: any[]): { sql: string; bindings: any[] } {
    const bindings: any[] = [];
    let pIdx = 1;
    let sql = `DELETE FROM ${this.wrap(table)}`;

    if (wheres && wheres.length > 0) {
      const whereClauses: string[] = [];
      for (const w of wheres) {
        bindings.push(w.value);
        whereClauses.push(`${w.boolean} ${this.wrap(w.column)} ${w.operator} ${this.getPlaceholder(pIdx++)}`);
      }
      sql += ' WHERE ' + whereClauses.join(' ').replace(/^(AND|OR)\s+/i, '');
    }

    return { sql, bindings };
  }
}

/**
 * MySQL Grammar (Backticks & '?' placeholders)
 */
export class MySqlGrammar extends BaseGrammar {
  wrapValue(value: string): string {
    return '`' + value.replace(/`/g, '``') + '`';
  }
  getPlaceholder(_index: number): string {
    return '?';
  }
}

/**
 * PostgreSQL Grammar (Double quotes & numbered '$1, $2' placeholders)
 */
export class PostgresGrammar extends BaseGrammar {
  wrapValue(value: string): string {
    return '"' + value.replace(/"/g, '""') + '"';
  }
  getPlaceholder(index: number): string {
    return `$${index}`;
  }
}

/**
 * SQLite Grammar (Double quotes & '?' placeholders)
 */
export class SqliteGrammar extends BaseGrammar {
  wrapValue(value: string): string {
    return '"' + value.replace(/"/g, '""') + '"';
  }
  getPlaceholder(_index: number): string {
    return '?';
  }
}
