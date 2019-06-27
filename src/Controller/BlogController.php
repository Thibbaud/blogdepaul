<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     * @IsGranted("ROLE_USER")
     * 
     */
    public function index(ArticleRepository $repo)
    {
        return $this->render('blog/index.html.twig', [
            'articles' => $repo->findAll()
        ]);
    }

    /**
     * @Route("/blog/article/{id}", name="blog_show")
     * @IsGranted("ROLE_USER")
     */
    public function show(Article $article, Request $request, ObjectManager $manager) {
        $comments = $article->getComments();
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $comment->setArticle($article);
            $comment->setAuthor($user);
            $manager->persist($comment);
            $manager->flush();

            $this->addFlash('success', "Votre commentaire a bien été déposé !");
        }

        return $this->render('blog/show.html.twig', [
            'article' => $article,
            'comments' => $comments,
            'form' => $form->createView()
        ]);
}

    /**
     * 
     * @Route("/blog/create", name="blog_create")
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request, ObjectManager $manager) {
        $user = $this->getUser();
        
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $article->setAuthor($user);
            $manager->persist($article);
            $manager->flush();

            $this->addFlash('success', "Votre article a bien été posté !");

            return $this->redirectToRoute('blog');
        }

        return $this->render('blog/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
