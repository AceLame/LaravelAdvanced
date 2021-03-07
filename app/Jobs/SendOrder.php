<?php

namespace App\Jobs;

use App\Models\Order;
use App\Notifications\InvoicePaid;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;
use function PHPUnit\Framework\throwException;

class SendOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The product instance.
     *
     * @var Order
     */
    public $order;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     *
     * @param Order $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // get store data
        // get external server data & auth
        // load order products
        // format the data to be sent
        // if external server needs soap then (getPublicIp) using soap
        // push the request to store
        // receive the response

        // handle response
        // validate(response)
        // if response is not valid
        //      mark order as rejected and send (store didn't comply to docs) notification to admins
        //      fail the job entirely
        // else
        //      if response is success
        //          mark order as accepted and send notification to (user - manager)
        //      else
        //          mark order as REPOS rejected and send notification to (admin - manager - customer)

        try {
            $this->order->update(['tries' => ++$this->order->tries]);
            Notification::route('mail', 'et.azm112@gmail.com')->notify(new InvoicePaid($this->order));
        } catch (\Exception $exception) {
            $this->release(); // on timed out
            $this->fail($exception); // on hard failure
        }
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        if ($exception instanceof \Illuminate\Queue\MaxAttemptsExceededException)
            $this->order->update(['tries' => '0', 'user_name' => $this->order->user_name . ' - ' . $this->order->tries]);
        else
            $this->order->user_name = $exception->getMessage();
    }


    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [(new WithoutOverlapping($this->order->id))->dontRelease()];
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return DateTime
     */
//    public function retryUntil(): DateTime
//    {
//        return now()->addMinutes(10);
//    }

}
