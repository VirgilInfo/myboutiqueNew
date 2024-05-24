<?php

namespace App\Controller;

use App\Entity\ResetPassword;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ResetPasswordRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordController extends AbstractController
{

    private $passwordHasher;
    private $manager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $manager)
    {
        $this->passwordHasher = $passwordHasher;
        $this->manager = $manager;
    }


    #[Route('/mot-de-passe-oublie', name: 'reset_password')]
    public function index(Request $request, UserRepository $repo): Response
    {

        if ($this->getUser()) {

            return $this->redirectToRoute('account');
        }

        // dd($request->get('email'));

        if ($request->get('email')) {

            $user = $repo->findOneByEmail($request->get('email'));

            if ($user) {

                $resetPassword = new ResetPassword();
                $resetPassword->setUser($user)
                    ->setToken(uniqid())
                    ->setCreatedAt(new \datetime());

                $this->manager->persist($resetPassword);
                $this->manager->flush();

                // générer une route
                $url = $this->generateUrl('update_password', ['token' => $resetPassword->getToken()]);

                $contentEmail = 'Réinitialisation du mail, cliquez sur le lien ci-dessous<br>
                <a href="' . $_SERVER['HTTP_ORIGIN'] . $url . '">Réinitialisation du mot de passe</a>';

                mail($user->getEmail(), 'Réinitialisation mdp', $contentEmail);

                $this->addFlash(
                    'success',
                    'Vous allez recevoir un email ' . $_SERVER['HTTP_ORIGIN'] . $url . ' avec la procédure de réinitialisation'
                );

                return $this->redirectToRoute('home');
            } else {

                $this->addFlash(
                    'danger',
                    'L\'email ' . $request->get('email') . ' n\'existe pas, veuillez créer un compte'
                );

                return $this->redirectToRoute('register');
            }
        }





        return $this->render('reset_password/resetPassword.html.twig', [
            'controller_name' => 'ResetPasswordController',
        ]);
    }


    #[Route('/modifier-mot-de-passe/{token}', name: 'update_password')]
    public function update($token, ResetPasswordRepository $repo, Request $request): Response
    {

        $resetPassword = $repo->findOneByToken($token);

        if (!$resetPassword) {
            $this->addFlash(
                'danger',
                'L\'url est incorrecte'
            );

            return $this->redirectToRoute('home');
        }

        $dateCreate = $resetPassword->getCreatedAt();
        // dump($dateCreate->format('Y-m-d H:i'));

        //  $dateCreate->modify('+1 hour');
        // dd($dateCreate->format('Y-m-d H:i'));

        $now = new \datetime();

        if ($now > $dateCreate->modify('+1 hour')) {

            $this->addFlash(
                'danger',
                'La demande de modification a expirée'
            );

            return $this->redirectToRoute('reset_password');
        }

        $user = $resetPassword->getUser();

        $form = $this->createForm(ResetPasswordType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // hash the password (based on the security.yaml config for the $user class)
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $user->getnewPassword()
            );
            $user->setPassword($hashedPassword);

            // persiste les données dans le temps
            $this->manager->persist($user);

            //ecrit dans la bdd
            $this->manager->flush();

            $this->addFlash(
                'success',
                'Le mot de passe à bien été modifié'
            );

            return $this->redirectToRoute('app_login');
        }


        return $this->render('reset_password/updatePassword.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
