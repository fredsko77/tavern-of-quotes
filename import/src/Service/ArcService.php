<?php

namespace Import\Service;

use App\Entity\Answer;
use App\Entity\Arc;
use App\Entity\Question;
use App\Entity\Quote;
use App\Repository\QuoteRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ArcService
{
    use ImportTrait;

    public function __construct(
        private SluggerInterface $slugger,
        private EntityManagerInterface $manager,
        private QuoteRepository $quoteRepository
    ) {
    }

    /**
     * [create Quote objects and add them to Arc]
     *
     * @param UploadedFile $file
     * @param Arc $arc
     * 
     * @return Arc|null
     * 
     */
    public function import(UploadedFile $file, Arc $arc): ?Arc
    {
        $data = $this->getDataFromCsv($file);
        $starter =  null;

        if ($arc->getId() !== null) {
            $starter = (int) $this->quoteRepository->findLastQuoteByArc($arc)[1];
        }

        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $quote = new Quote;
                $position = $v->id;
                if (is_int($starter)) {
                    $starter++;
                    $position = $starter;
                }
                $quote
                    ->setPosition($position)
                    ->setContent($v->citation)
                    ->setCoeur((bool) $v->coeur)
                    ->setCreatedAt(new DateTimeImmutable);

                $quote->addAnswer((new Answer())->setLabel($v->reponse));
                $arc->addQuote($quote);
            }
        }

        return $arc;
    }
}
