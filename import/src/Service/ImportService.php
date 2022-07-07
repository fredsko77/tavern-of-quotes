<?php

namespace Import\Service;

use App\Entity\Answer;
use App\Entity\Arc;
use App\Entity\Question;
use App\Entity\Quote;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportService
{

    public function __construct(
        private SluggerInterface $slugger,
        private EntityManagerInterface $manager
    ) {
    }

    public function importQuotes(UploadedFile $file, Arc $arc): void
    {
        $arc
            ->setSlug($this->slugger->slug($arc->getName()))
            ->setCreatedAt(new DateTimeImmutable);

        $data = [];
        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            $i = 0;
            while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $data[$i] = $row;
                $i++;
            }
            fclose($handle);
        }
        unset($data[0]);

        foreach ($data as $k => $v) {
            $quote = new Quote;
            $v[1] = str_replace('"', '', $v[1]);
            $v[1] = preg_replace("/-/", "", $v[1], 1);
            $quote
                ->setPosition($v[0])
                ->setContent($v[1])
                ->setCoeur((bool) $v[3])
                ->setCreatedAt(new DateTimeImmutable);

            $quote->addAnswer((new Answer())->setLabel($v[2]));
            $arc->addQuote($quote);
        }

        $this->manager->persist($arc);
        $this->manager->flush();

        return;
    }
}
