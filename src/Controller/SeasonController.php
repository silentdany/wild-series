<?php

namespace App\Controller;

use App\Entity\Season;
use App\Form\SeasonType;
use App\Repository\SeasonRepository;
use App\Service\Slugify;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

/** 
 * @Route("/seasons", name="season_")
 */
class SeasonController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
    */
    public function index(SeasonRepository $seasonRepository): Response
    {
        return $this->render('season/index.html.twig', [
            'seasons' => $seasonRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET", "POST"})
    */
    public function new(Request $request, Slugify $slugify, MailerInterface $mailer): Response
    {
        $season = new Season();
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $slug = $slugify->generate($season->getNumber());
            $season->setSlug($slug);
            $entityManager->persist($season);
            $entityManager->flush();
            
            $program = $season->getProgram();
            $subject = 'Une nouvelle saison de '. $program->getTitle() .' vient d\'être publiée !';
            $email = (new TemplatedEmail())
                ->from($this->getParameter('mailer_from'))
                ->to('email@test.com')
                ->subject($subject)
                ->htmlTemplate('season/newSeasonEmail.html.twig')
                ->context([
                    'season' => $season
                ]);

        $mailer->send($email);
            return $this->redirectToRoute('program_index');
        }

        return $this->render('season/new.html.twig', [
            'season' => $season,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="show", methods={"GET"})
     */
    public function show(Season $season): Response
    {
        return $this->render('season/show.html.twig', [
            'season' => $season,
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Season $season, Slugify $slugify): Response
    {
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugify->generate($season->getNumber());
            $season->setSlug($slug);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('program_index');
        }

        return $this->render('season/edit.html.twig', [
            'season' => $season,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="delete", methods={"POST"})
     */
    public function delete(Request $request, Season $season): Response
    {
        if ($this->isCsrfTokenValid('delete'.$season->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($season);
            $entityManager->flush();
        }

        return $this->redirectToRoute('program_index');
    }
}
