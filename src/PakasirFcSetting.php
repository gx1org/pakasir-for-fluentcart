<?php

namespace PakasirFc;

use FluentCart\App\Modules\PaymentMethods\Core\BaseGatewaySettings;

class PakasirFcSetting extends BaseGatewaySettings
{
  public $methodHandler = 'fluent_cart_payment_settings_pakasir';

  public function get($key = '')
  {
    $settings = $this->settings;

    if ($key && isset($this->settings[$key])) {
      return $this->settings[$key];
    }
    return $settings;
  }

  public function getMode(): string
  {
    return 'live';
  }

  public function isActive(): bool
  {
    return $this->get('is_active') === 'yes';
  }

  public function getDefaults(): array
  {
    return $this->defaults();
  }

  public function defaults(): array
  {
    return [
      'is_active'       => 'no',
      'pakasir_slug'    => '',
      'pakasir_api_key' => '',
    ];
  }
}
