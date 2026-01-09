<?php

namespace PakasirFc;

use FluentCart\App\Modules\PaymentMethods\Core\AbstractPaymentGateway;
use FluentCart\App\Services\Payments\PaymentInstance;
use FluentCart\App\Models\Order;
use FluentCart\App\Helpers\StatusHelper;

class PakasirFcGateway extends AbstractPaymentGateway
{
  public array $supportedFeatures = ['payment', 'webhook'];

  public function __construct()
  {
    parent::__construct(new PakasirFcSetting());
  }

  public function meta(): array
  {
    return [
      'brand_color' => 'orange',
      'icon' => 'https://upld.zone.id/uploads/quay/pakasir.webp',
      'logo' => 'https://upld.zone.id/uploads/quay/pakasir.webp',
      'upcoming' => false,
      'title' => __('Pakasir', 'pakasir-for-fluentcart'),
      'route' => 'pakasir',
      'slug' => 'pakasir',
      'description' => __('Bayar dengan QRIS, Virtual Account, dll (via Pakasir)', 'pakasir-for-fluentcart'),
      'status' => $this->settings->get('is_active') === 'yes',
    ];
  }

  public function makePaymentFromPaymentInstance(PaymentInstance $payment)
  {
    if ($payment->transaction->currency !== 'IDR') {
      return [
        'status' => 'failed',
        'message' => 'Pakasir hanya melayani mata uang Rupiah (IDR)'
      ];
    }

    $amount = $payment->transaction->total / 100;
    $orderId = $payment->order->id;
    $slug = $this->settings->get('pakasir_slug');
	$redirect =  home_url()."/shop";
    $payUrl = "https://app.pakasir.com/pay/$slug/$amount?order_id=$orderId&redirect=$redirect";
    return [
      'status' => 'success',
      'redirect_to' => $payUrl,
      'message' => 'Redirecting...'
    ];
  }

  public function fields(): array
  {
    return [
      'pakasir_slug' => [
        'type' => 'text',
        'label' => __('Pakasir Slug', 'pakasir-for-fluentcart'),
      ],
      'pakasir_api_key' => [
        'type' => 'password',
        'label' => __('Pakasir API Key', 'pakasir-for-fluentcart'),
      ]
    ];
  }

  public function getOrderInfo($orderId): array
  {
    $order = fluentcrm_get_order($orderId);

    return [
      'id'     => $orderId,
      'total'  => $order->total,
      'status' => $order->status,
    ];
  }

  public function handleIPN()
  {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || empty($data['order_id'])) {
      wp_die('Invalid webhook', 400);
    }

    // Verify to Pakasir
    $url = sprintf(
      'https://app.pakasir.com/api/transactiondetail?project=%s&order_id=%s&amount=%s&api_key=%s',
      $this->settings->get('pakasir_slug'),
      $data['order_id'],
      $data['amount'],
      $this->settings->get('pakasir_api_key')
    );
    $verify = wp_remote_get($url);
    $response = json_decode(wp_remote_retrieve_body($verify), true);

    if (
      isset($response['transaction']) &&
      $response['transaction']['status'] === 'completed'
    ) {
      // Mark order paid
      $order = Order::find(intval($data['order_id']));
      $transaction = $order->transactions()->where('payment_method', 'pakasir')->first();
      $transaction->status = 'succeeded';
      $transaction->save();

      $statusHelper = new StatusHelper($order);
      $statusHelper->syncOrderStatuses($transaction);

      status_header(200);
      echo wp_json_encode([
        'success' => true,
        'message' => 'Callback processed'
      ]);
      exit;
    }

    wp_die('OK');
  }

  public function getEnqueueScriptSrc($hasSubscription = 'no'): array
  {
    return [
      [
        'handle' => 'pakasir-checkout',
        'src' => PAKASIR_FC_URL . 'assets/pakasir-checkout.js',
        'version' => "1"
      ]
    ];
  }
}
