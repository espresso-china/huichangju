<?php


namespace Xndl\YsePay\YsePay\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Xndl\YsePay\Ledger\Ledger;

/**
 * Class MenuServiceProvider.
 */
class LedgerServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple['ledger'] = function ($pimple) {
            return new Ledger($pimple['config']);
        };
    }
}
