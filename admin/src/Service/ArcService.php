<?php

namespace Admin\Service;

use App\Entity\Arc;
use DateTimeImmutable;
use App\Repository\ArcRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Import\Service\ArcService as ServiceArcService;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class ArcService
{

    private const ARC_UPLOAD_DIR = 'uploads/arc';

    public function __construct(
        private ArcRepository $repository,
        private EntityManagerInterface $manager,
        private SluggerInterface $slugger,
        private ContainerBagInterface $container,
        private ServiceArcService $arcService,
        private Filesystem $filesystem
    ) {
    }

    public function index(): array
    {
        $arcs = $this->repository->findAll();

        return compact('arcs');
    }

    public function store(Arc $arc, ?UploadedFile $file = null, ?UploadedFile $image = null): void
    {
        if (is_null($arc->getId())) {
            $arc->setCreatedAt(new DateTimeImmutable);
        } else {
            $arc->setUpdatedAt(new DateTimeImmutable);
        }

        $arc->setSlug($this->slugger->slug($arc->getName()));

        if (is_null($arc->getPosition())) {
            $maxPasition = (int) $this->repository->findMaxPosition()[1];
            $pos = $maxPasition + 1;
            $arc->setPosition($pos);
        } else {
            $posExists = $this->repository->findOneBy(['position' => $arc->getPosition()]);
            if ($posExists instanceof Arc) {
                $greaterThan = $this->repository->findPositionGreaterThan($arc->getPosition());

                foreach ($greaterThan as $exist) {
                    $exist->setPosition($exist->getPosition() + 1);
                    $this->manager->persist($exist);
                }
            }
        }

        if ($file instanceof UploadedFile) {
            $arc = $this->arcService->import($file, $arc);
        }

        if ($image instanceof UploadedFile) {
            $arc = $this->uploadImage($image, $arc);
        }

        $this->manager->persist($arc);
        $this->manager->flush();
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
    private function uploadImage(?UploadedFile $image = null, Arc $arc): Arc
    {
        if ($image instanceof UploadedFile) {
            $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            // this is needed to safely include the file name as part of the URL
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();
            // this is the upload files directory
            $dir = $this->getPublicDir() . self::ARC_UPLOAD_DIR;

            // Create Upload directory if not exists
            if (!$this->filesystem->exists($dir)) {
                $this->filesystem->mkdir($dir);
            }

            // delete Image
            $this->deleteImage($arc);

            // Move the file to the directory where brochures are stored
            try {
                $image->move(
                    $dir,
                    $newFilename
                );
                $arc->setImage(self::ARC_UPLOAD_DIR . '/' . $newFilename);
            } catch (FileException $e) {
                throw new FileException('An error occured while storing the image file !' . $e->getMessage());
            }
        }

        return $arc;
    }

    /**
     * @return mixed
     */
    private function getPublicDir(): mixed
    {
        return $this->container->get('public_dir');
    }

    public function delete(Arc $arc): void
    {
        $this->deleteImage($arc);

        $this->manager->remove($arc);
        $this->manager->flush();
    }

    private function deleteImage(Arc $arc)
    {
        // Remove the current uploaded image before uploaded the new one 
        if (is_string($arc->getImage())) {
            $this->filesystem->remove($this->getPublicDir() . $arc->getImage());
        }
    }
}
