/**
 * NovaFlow Fastify - Fluent Validator Engine
 * Supports standard Laravel/PHP-style rules: required, email, min, max, numeric, in
 */

export interface ValidationResult {
  passes: boolean;
  fails: boolean;
  errors: Record<string, string[]>;
}

export class Validator {
  public static make(data: Record<string, any>, rules: Record<string, string>): ValidationResult {
    const errors: Record<string, string[]> = {};

    for (const field in rules) {
      const fieldRules = rules[field].split('|');
      const val = data[field];

      for (const rule of fieldRules) {
        if (rule === 'required') {
          if (val === undefined || val === null || val === '') {
            this.addError(errors, field, `The ${field} field is required.`);
          }
        } else if (rule === 'email') {
          if (val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(val))) {
            this.addError(errors, field, `The ${field} must be a valid email address.`);
          }
        } else if (rule.startsWith('min:')) {
          const min = parseInt(rule.split(':')[1], 10);
          if (typeof val === 'string' && val.length < min) {
            this.addError(errors, field, `The ${field} must be at least ${min} characters.`);
          } else if (typeof val === 'number' && val < min) {
            this.addError(errors, field, `The ${field} must be at least ${min}.`);
          }
        } else if (rule.startsWith('max:')) {
          const max = parseInt(rule.split(':')[1], 10);
          if (typeof val === 'string' && val.length > max) {
            this.addError(errors, field, `The ${field} may not be greater than ${max} characters.`);
          } else if (typeof val === 'number' && val > max) {
            this.addError(errors, field, `The ${field} may not be greater than ${max}.`);
          }
        } else if (rule === 'numeric') {
          if (val !== undefined && val !== null && isNaN(Number(val))) {
            this.addError(errors, field, `The ${field} must be a number.`);
          }
        } else if (rule.startsWith('in:')) {
          const allowed = rule.split(':')[1].split(',');
          if (val && !allowed.includes(String(val))) {
            this.addError(errors, field, `The selected ${field} is invalid. Allowed: ${allowed.join(', ')}`);
          }
        }
      }
    }

    const hasErrors = Object.keys(errors).length > 0;
    return {
      passes: !hasErrors,
      fails: hasErrors,
      errors
    };
  }

  private static addError(errors: Record<string, string[]>, field: string, msg: string): void {
    if (!errors[field]) {
      errors[field] = [];
    }
    errors[field].push(msg);
  }
}
