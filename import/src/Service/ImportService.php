<?php
namespace Import\Service;

use App\Entity\Arc;
use App\Entity\Question;
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
    ){}

    public function importQuestions(UploadedFile $file, Arc $arc):void {
        $arc
            ->setSlug(strtolower($this->slugger->slug($arc->getName())))
            ->setCreatedAt(new DateTimeImmutable)    
        ;

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
            $question = new Question;
            $v[1] = str_replace('"', '', $v[1]);
            $v[1] = preg_replace("/-/", "", $v[1], 1);
            $question
                ->setPosition($v[0])
                ->setName($v[1])
                ->setAnswer($v[2])
                ->setCoeur((bool) $v[3])
                ->setCreatedAt(new DateTimeImmutable)
            ;

            $arc->addQuestion($question);
        }

        $this->manager->persist($arc);
        $this->manager->flush();

        return;
    }

}