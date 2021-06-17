<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Form\ProgramType;
use App\Repository\EpisodeRepository;
use App\Repository\ProgramRepository;
use App\Repository\SeasonRepository;
use App\Service\Slugify;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

/** 
 * @Route("/programs", name="program_")
 */
class ProgramController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
    */
    public function index(ProgramRepository $programRepository): Response
    {
        return $this->render('program/index.html.twig', [
            'programs' => $programRepository->findAll()
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET", "POST"})
    */
    public function new(Request $request, Slugify $slugify, MailerInterface $mailer) : Response
    {
        $program = new Program();
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);
            $entityManager->persist($program);
            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from($this->getParameter('mailer_from'))
                ->to('email@test.com')
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->htmlTemplate('program/newProgramEmail.html.twig')
                ->context([
                    'program' => $program
                ]);

        $mailer->send($email);

            return $this->redirectToRoute('program_index');
            
        }
        return $this->render('program/new.html.twig', [
            'program' => $program,
            "form" => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="show", methods={"GET"})
     */
    public function show(Program $program): Response
    {
        if (!$program) {
        throw $this->createNotFoundException(
            'No program id n°'.$program.' found in program\'s table.'
        );
    }

    return $this->render('program/show.html.twig', [
        'program' => $program,
        'seasons' => $program->getSeasons()
    ]);
    }

    /**
     * @Route("/{slug}/season/{seasonNumber}", name="season_show", methods={"GET"})
     */
    public function seasonShow(Program $program, int $seasonNumber, SeasonRepository $seasonRepository): Response
    {
    if (!$program) {
        throw $this->createNotFoundException(
            'No program id n°'.$program.' found in program\'s table.'
        );
    }

    return $this->render('program/season_show.html.twig', [
        'program' => $program,        
        'season' => $seasonRepository->findOneBy(['number' => $seasonNumber])
    ]);
    }

    /**
     * @Route("/{slug}/edit", name="edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Program $program, Slugify $slugify): Response
    {
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('index');
        }

        return $this->render('program/edit.html.twig', [
            'program' => $program,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="delete", methods={"POST"})
     */
    public function delete(Request $request, Program $program): Response
    {
        if ($this->isCsrfTokenValid('delete'.$program->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($program);
            $entityManager->flush();
        }

        return $this->redirectToRoute('program_index');
    }
}
