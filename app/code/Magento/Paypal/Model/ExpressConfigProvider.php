<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Paypal\Helper\Data as PaypalHelper;

/**
 * Class ExpressConfigProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExpressConfigProvider implements ConfigProviderInterface
{
    const IN_CONTEXT_BUTTON_ID = 'paypal-express-in-context-button';

    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var PaypalHelper
     */
    protected $paypalHelper;

    /**
     * @var string[]
     */
    protected $methodCodes = [
        Config::METHOD_WPP_BML,
        Config::METHOD_WPP_PE_EXPRESS,
        Config::METHOD_WPP_EXPRESS,
        Config::METHOD_WPP_PE_BML
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ConfigFactory $configFactory
     * @param ResolverInterface $localeResolver
     * @param CurrentCustomer $currentCustomer
     * @param PaypalHelper $paypalHelper
     * @param PaymentHelper $paymentHelper
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        ConfigFactory $configFactory,
        ResolverInterface $localeResolver,
        CurrentCustomer $currentCustomer,
        PaypalHelper $paypalHelper,
        PaymentHelper $paymentHelper,
        UrlInterface $urlBuilder
    ) {
        $this->localeResolver = $localeResolver;
        $this->config = $configFactory->create();
        $this->currentCustomer = $currentCustomer;
        $this->paypalHelper = $paypalHelper;
        $this->paymentHelper = $paymentHelper;
        $this->urlBuilder = $urlBuilder;

        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $this->paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $locale = $this->localeResolver->getLocale();

        $config = [
            'payment' => [
                'paypalExpress' => [
                    'paymentAcceptanceMarkHref' => $this->config->getPaymentMarkWhatIsPaypalUrl(
                        $this->localeResolver
                    ),
                    'paymentAcceptanceMarkSrc' => $this->config->getPaymentMarkImageUrl(
                        $locale
                    ),
                    'isContextCheckout' => false,
                    'inContextConfig' => []
                ]
            ]
        ];

        $isInContext = $this->isInContextCheckout();
        if ($isInContext) {
            $config['payment']['paypalExpress']['isContextCheckout'] = $isInContext;
            $config['payment']['paypalExpress']['inContextConfig'] = [
                'inContextId' => self::IN_CONTEXT_BUTTON_ID,
                'merchantId' => $this->config->getValue('merchant_id'),
                'path' => $this->urlBuilder->getUrl('paypal/express/gettoken', ['_secure' => true]),
                'clientConfig' => [
                    'environment' => ((int) $this->config->getValue('sandbox_flag') ? 'sandbox' : 'production'),
                    'locale' => $locale,
                    'button' => [
                        self::IN_CONTEXT_BUTTON_ID
                    ],
                    'allowedFunding' => [],
                    'disallowedFunding' => $this->getDisallowedFunding(),
                    'styles' => $this->getButtonStyles($locale),
                    'getTokenUrl' => $this->urlBuilder->getUrl('paypal/express/getTokenData'),
                    'onAuthorizeUrl' => $this->urlBuilder->getUrl('paypal/express/onAuthorization'),
                    'onCancelUrl' => $this->urlBuilder->getUrl('paypal/express/cancel')
                ],
            ];
        }

        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $config['payment']['paypalExpress']['redirectUrl'][$code] = $this->getMethodRedirectUrl($code);
                $config['payment']['paypalExpress']['billingAgreementCode'][$code] =
                    $this->getBillingAgreementCode($code);
            }
        }

        return $config;
    }

    /**
     * @return bool
     */
    protected function isInContextCheckout()
    {
        $this->config->setMethod(Config::METHOD_EXPRESS);

        return (bool)(int) $this->config->getValue('in_context');
    }

    /**
     * Return redirect URL for method
     *
     * @param string $code
     * @return mixed
     */
    protected function getMethodRedirectUrl($code)
    {
        return $this->methods[$code]->getCheckoutRedirectUrl();
    }

    /**
     * Return billing agreement code for method
     *
     * @param string $code
     * @return null|string
     */
    protected function getBillingAgreementCode($code)
    {
        $customerId = $this->currentCustomer->getCustomerId();
        $this->config->setMethod($code);
        return $this->paypalHelper->shouldAskToCreateBillingAgreement($this->config, $customerId)
            ? Express\Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT : null;
    }

    /**
     * Returns button styles based on configuration
     *
     * @param string $locale
     * @return array
     */
    private function getButtonStyles($locale) : array
    {
        $this->config->setMethod(Config::METHOD_EXPRESS);

        $styles = [
            'layout' => 'vertical',
            'size' => 'responsive',
            'color' => 'gold',
            'shape' => 'rect',
            'label' => 'paypal'
        ];
        if (!!$this->config->getValue('checkout_page_button_customize')) {
            $styles['layout'] = $this->config->getValue('checkout_page_button_layout');
            $styles['size'] = $this->config->getValue('checkout_page_button_size');
            $styles['color'] = $this->config->getValue('checkout_page_button_color');
            $styles['shape'] = $this->config->getValue('checkout_page_button_shape');
            $styles['label'] = $this->config->getValue('checkout_page_button_label');

            $styles = $this->updateStyles($styles, $locale);
        }
        return $styles;
    }

    /**
     * Update styles based on locale and labels
     *
     * @param array $styles
     * @param string $locale
     * @return array
     */
    private function updateStyles($styles, $locale) : array
    {
        $installmentPeriodLocale = [
            'en_MX' => 'mx',
            'es_MX' => 'mx',
            'en_BR' => 'br',
            'pt_BR' => 'br'
        ];

        // Credit label cannot be used with any custom color option or vertical layout.
        if ($styles['label'] === 'credit') {
            $styles['color'] = 'darkblue';
            $styles['layout'] = 'horizontal';
        }

        // Installment label is only available for specific locales
        if ($styles['label'] === 'installment') {
            if (array_key_exists($locale, $installmentPeriodLocale)) {
                $styles['installmentperiod'] = (int)$this->config->getValue(
                    "checkout_page_button_{$installmentPeriodLocale[$locale]}_installment_period"
                );
            } else {
                $styles['label'] = 'paypal';
            }
        }

        return $styles;
    }

    /**
     * Returns disallowed funding from configuration
     *
     * @return array
     *
     */
    private function getDisallowedFunding() : array
    {
        $this->config->setMethod(Config::METHOD_EXPRESS);
        $disallowedFunding = $this->config->getValue('disable_funding_options');
        return $disallowedFunding ? explode(',', $disallowedFunding) : [];
    }
}
