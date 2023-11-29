<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\OpenMarketplace\Behat\Page\Vendor;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

final class CustomerDashboardPage extends SymfonyPage
{
    public function getRouteName(): string
    {
        return 'sylius_shop_account_dashboard';
    }

    public function itemWithValueExistsInsideSidebar($value): bool
    {
        $sidebars = $this->getDocument()->findAll('css', '.grid .four .menu');
        foreach ($sidebars as $sidebar) {
            $links = $sidebar->findAll('css', '.item');
            foreach ($links as $link) {
                if ($value === $link->getText()) {
                    return true;
                }
            }
        }

        return false;
    }
}
