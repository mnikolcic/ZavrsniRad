<?php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;


#[Route('/admin', name: 'admin.posts')]
class PostsController extends AbstractController

{
    
    #[Route('/post/create', name: '.create')]
    
    public function create(Request $request, PostRepository $repo, SluggerInterface $slugger): Response
    {
        $post = new Post();
        $post->setUser($this->getUser());

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $post = $form->getData();

            $image = $form->get('image')->getData();
            $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

            try 
            {
                $image->move(
                    $this->getParameter('image_directory'),
                    $newFilename
                );
            }
            catch (FileException $e) 
            {
                throw new Exception('failed to upload, Try again later');
            }

            $post->setImage($newFilename);
            $repo->save($post, true);

            return $this->redirectToRoute('admin.posts.index');
        }

        return $this->render('posts/create.html.twig', [
            'form' => $form,
        ]);
    }

    
    #[Route('/posts', name: '.index')]
    public function index(PostRepository $repo): Response
    {
        $posts = $repo->findAll();
        
        return $this->render('posts/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    
    #[Route('/post/{id}/edit', name: '.edit')]
    
    public function edit(Request $request, Post $post, PostRepository $repo, SluggerInterface $slugger): Response
    {
        
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $post = $form->getData();
           if ($form->get("image")->getData() !== null) {
            $image = $form->get('image')->getData();
            $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

            try 
            {
                $image->move(
                    $this->getParameter('image_directory'),
                    $newFilename
                );
            }
            catch (FileException $e) 
            {
                throw new Exception('failed to upload, Try again later');
            }

            $post->setImage($newFilename);
        }
            $repo->save($post, true);

            return $this->redirectToRoute('admin.posts.index');
        }

        return $this->render('posts/create.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    
    #[Route('/post/{id}/delete', name: '.delete')]
    public function delete(Request $request, Post $post, PostRepository $repo): Response
    {
        $repo->remove($post, true);

        return $this->redirectToRoute('admin.posts.index');
    }
}