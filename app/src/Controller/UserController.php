<?php

/**
 * User Controller.
 */

namespace App\Controller;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Form\Type\UserRegistrationType;
use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UserController.
 */
#[Route('/user')]
class UserController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param TranslatorInterface         $translator     Translator interface
     * @param UserPasswordHasherInterface $passwordHasher PasswordHasher interface
     * @param UserServiceInterface        $userService    User service interface
     */
    public function __construct(private readonly TranslatorInterface $translator, private readonly UserPasswordHasherInterface $passwordHasher, private readonly UserServiceInterface $userService)
    {
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/create', name: 'user_create', methods: 'GET|POST')]
    public function create(Request $request): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED')) {
            return $this->redirectToRoute('user_create'); // TODO change redirect route
        }

        $user = new User();
        $user->setRoles([UserRole::ROLE_USER->value]);

        $form = $this->createForm(
            UserRegistrationType::class,
            $user,
            ['action' => $this->generateUrl('user_create')]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $user->getPassword();
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
            $this->userService->save($user);

            $this->addFlash('success', $this->translator->trans('message.created_successfully'));

            return $this->redirectToRoute('user_create'); // TODO change redirect route
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }
}
