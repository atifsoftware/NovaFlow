/**
 * NovaFlow Fastify - Asynchronous Background Job Queue
 * Handles non-blocking email notifications, report generation, and data syncing
 */

export interface Job {
  id: string;
  name: string;
  payload: any;
  delayMs?: number;
  createdAt: number;
}

export type JobHandler = (payload: any) => Promise<void> | void;

export class Queue {
  private static queue: Job[] = [];
  private static handlers = new Map<string, JobHandler>();
  private static isProcessing = false;

  /**
   * Register a job handler
   */
  public static handle(name: string, handler: JobHandler): void {
    this.handlers.set(name, handler);
  }

  /**
   * Push job to queue
   */
  public static push(name: string, payload: any, delayMs: number = 0): string {
    const id = `job_${Date.now()}_${Math.random().toString(36).substr(2, 6)}`;
    const job: Job = {
      id,
      name,
      payload,
      delayMs,
      createdAt: Date.now()
    };
    this.queue.push(job);

    // Auto trigger background worker tick
    setImmediate(() => this.processNext());
    return id;
  }

  /**
   * Process next job in worker loop
   */
  private static async processNext(): Promise<void> {
    if (this.isProcessing || this.queue.length === 0) return;
    this.isProcessing = true;

    const job = this.queue.shift();
    if (job) {
      const handler = this.handlers.get(job.name);
      if (handler) {
        try {
          if (job.delayMs && job.delayMs > 0) {
            await new Promise(res => setTimeout(res, job.delayMs));
          }
          await handler(job.payload);
        } catch (err) {
          console.error(`[QueueWorker] Failed to process job ${job.id} (${job.name}):`, err);
        }
      } else {
        console.warn(`[QueueWorker] No handler registered for job: ${job.name}`);
      }
    }

    this.isProcessing = false;
    if (this.queue.length > 0) {
      setImmediate(() => this.processNext());
    }
  }

  public static size(): number {
    return this.queue.length;
  }
}

// Register default system handlers
Queue.handle('send_welcome_email', async (payload: { email: string; name: string }) => {
  // Simulates background email sending
});

Queue.handle('generate_invoice_pdf', async (payload: { invoiceId: number }) => {
  // Simulates background PDF creation
});
