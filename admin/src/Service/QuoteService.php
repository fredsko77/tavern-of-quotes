<?php

namespace Admin\Service;

use App\Entity\Arc;
use App\Entity\Quote;
use DateTimeImmutable;
use App\Repository\QuoteRepository;
use App\Utils\HelperTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class QuoteService
{

    use HelperTrait;

    public const ARC_UPLOAD_DIR = '/uploads/arc';

    public function __construct(
        private QuoteRepository $quoteRepository,
        private EntityManagerInterface $manager,
        private SluggerInterface $slugger,
        private ContainerBagInterface $container,
        private Filesystem $filesystem
    ) {
    }

    public function delete(Quote $quote): void
    {
        $this->quoteRepository->remove($quote);
        $this->manager->flush();

        return;
    }

    public function store(Quote $quote, ?UploadedFile $image = null, ?Arc $arc = null): void
    {
        $now = new DateTimeImmutable;
        $quote->getId() ? $quote->setUpdatedAt($now) : $quote->setCreatedAt($now);
        $quote->setSlug(
            $this->slugger->slug(
                $this->skipAccents(
                    $quote->getContent()
                )
            )
        );

        foreach ($quote->getAnswers() as $key => $answer) {
            if (is_null($answer->getQuote())) {
                $answer->setQuote($quote);
            }
        }

        // dd($quote->getAnswers()); 

        if ($image instanceof UploadedFile) {
            $quote = $this->uploadImage($image, $quote);
        }

        if ($arc instanceof Arc) {
            $arc->addQuote($quote);
            $this->manager->persist($arc);
        } else {
        }

        $this->manager->flush();

        return;
    }


    /**
     * [uploadImage physically in the app]
     *
     * @param UploadedFile|null $image
     * @param Arc $arc
     * 
     * @return void
     * 
     */
    private function uploadImage(?UploadedFile $image = null, Quote $quote): Quote
    {
        if ($image instanceof UploadedFile) {
            $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            // this is needed to safely include the file name as part of the URL
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();
            // this is the upload files directory
            $dir = $this->getPublicDir() . self::ARC_UPLOAD_DIR . '/' . $quote->getArc()->getId();

            // Create Upload directory if not exists
            if (!$this->filesystem->exists($dir)) {
                $this->filesystem->mkdir($dir);
            }

            // delete Image
            $this->deleteImage($quote);

            // Move the file to the directory where brochures are stored
            try {
                $image->move(
                    $dir,
                    $newFilename
                );
                $quote->setImage(self::ARC_UPLOAD_DIR . '/' . $newFilename);
            } catch (FileException $e) {
                throw new FileException('An error occured while storing the image file !' . $e->getMessage());
            }
        }

        return $quote;
    }


    /**
     * @return mixed
     */
    private function getPublicDir(): mixed
    {
        return $this->container->get('public_dir');
    }

    private function deleteImage(Quote $quote)
    {
        // Remove the current uploaded image before uploaded the new one 
        if (is_string($quote->getImage())) {
            $this->filesystem->remove($this->getPublicDir() . $quote->getImage());
        }
    }
}
