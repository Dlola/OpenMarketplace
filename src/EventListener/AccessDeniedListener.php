<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\OpenMarketplace\EventListener;

use BitBag\OpenMarketplace\Exception\ShopUserHasNoVendorContextException;
use BitBag\OpenMarketplace\Exception\ShopUserNotFoundException;
use BitBag\OpenMarketplace\Provider\VendorProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\RouterInterface;

class AccessDeniedListener implements EventSubscriberInterface
{
    private VendorProviderInterface $vendorProvider;

    private RequestStack $requestStack;

    private RouterInterface $router;

    public function __construct(
        VendorProviderInterface $vendorProvider,
        RequestStack $requestStack,
        RouterInterface $router
    ) {
        $this->vendorProvider = $vendorProvider;
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 2],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof AccessDeniedHttpException) {
            return;
        }

        $currentRequest = $this->requestStack->getCurrentRequest();
        $uriParts = explode('/', $currentRequest->getRequestUri());
        if ($uriParts[0] === "") {
            array_shift($uriParts);
        }

        if ($uriParts < 4) {
            return;
        }

        if ($uriParts[1] !== 'account' || $uriParts[2] !== 'vendor' || $uriParts[3] === 'conversations') {
            return;
        }

        try {
            $currentVendor = $this->vendorProvider->provideCurrentVendor();
            if ($currentVendor->isEnabled() === false) {
                $event->setResponse(new RedirectResponse(
                    $this->router->generate('open_marketplace_vendor_conversation_index')
                ));
                $event->stopPropagation();
            }
        } catch (ShopUserHasNoVendorContextException|ShopUserNotFoundException $e) {

        }
    }
}
