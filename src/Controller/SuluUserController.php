<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\SyliusProducerPlugin\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\User\Canonicalizer\CanonicalizerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * @Rest\RouteResource("sulu-user")
 * @Rest\NamePrefix("sulu_sylius_api_")
 */
class SuluUserController extends FOSRestController
{
    /**
     * @var AuthenticationProviderInterface
     */
    private $authenticationProvider;

    public function __construct(
        AuthenticationProviderInterface $authenticationProvider
    ) {
        $this->authenticationProvider = $authenticationProvider;
    }

    public function getAction(Request $request) {
        $email = $request->get('email');
        $plainPassword = $request->get('password');

        $data = null;

        $token = new UsernamePasswordToken($email, $plainPassword, 'shop');
        $result = $this->authenticationProvider->authenticate($token);

        if ($result) {
            $user = $result->getUser();
            $data = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
                'gender' => $user->getCustomer()->getGender(),
                'firstName' => $user->getCustomer()->getFirstName(),
                'lastName' => $user->getCustomer()->getLastName(),
                'email' => $user->getCustomer()->getEmail(),
            ];
        }

        return $this->getViewHandler()->handle(new View($data));
    }

    public function tokenAction($userId)
    {
        // TODO: Implement it
    }
}