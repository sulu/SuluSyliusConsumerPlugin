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
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * @Rest\RouteResource("sulu-user")
 * @Rest\NamePrefix("sulu_sylius_api_")
 */
class SuluUserController extends FOSRestController
{
    /**
     * @var CanonicalizerInterface
     */
    private $canonicalizer;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        CanonicalizerInterface $canonicalizer,
        EncoderFactoryInterface $encoderFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->canonicalizer = $canonicalizer;
        $this->encoderFactory = $encoderFactory;
        $this->customerRepository = $customerRepository;
    }

    public function getAction(Request $request) {
        $email = $request->get('email');
        $plainPassword = $request->get('password');

        $data = null;

        $user = $this->findUser($email, $plainPassword);
        if ($user) {
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

    private function findUser(string $email, string $plainPassword): ?ShopUserInterface
    {
        $canonicalEmail = $this->canonicalizer->canonicalize($email);
        /** @var CustomerInterface $customer */
        $customer = $this->customerRepository->findOneBy(
            [
                'emailCanonical' => $canonicalEmail,
            ]
        );

        if (!$customer) {
            return null;
        }

        $user = $customer->getUser();
        if (!$user) {
            return null;
        }

        $encoder = $this->encoderFactory->getEncoder(get_class($user));
        $validPassword = $encoder->isPasswordValid(
            $user->getPassword(),
            $plainPassword,
            $user->getSalt()
        );
        if (!$validPassword) {
            return null;
        }

        return $user;
    }

    public function tokenAction($userId)
    {
        // TODO: Implement it
    }
}