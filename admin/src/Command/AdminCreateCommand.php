<?php

namespace Admin\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsCommand(
    name: 'admin:create',
    description: 'Create an admin user',
)]
class AdminCreateCommand extends Command
{

    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private EntityManagerInterface $manager,
        private SluggerInterface $slugger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::OPTIONAL, 'Admin username')
            ->addArgument('email', InputArgument::OPTIONAL, 'Admin email')
            ->addArgument('password', InputArgument::OPTIONAL, 'Admin password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $io = new SymfonyStyle($input, $output);

        $username = $input->getArgument('username');

        if (!$username) {
            $question = new Question('Admin username : ');
            $username = $helper->ask($input, $output, $question);
        }

        $email = $input->getArgument('email');
        if (!$email) {
            $question = new Question(ucfirst($username) . ' email : ');
            $email = $helper->ask($input, $output, $question);
        }

        $password = $input->getArgument('password');
        if (!$password) {
            $question = new Question(ucfirst($username) . ' password : ');
            $password = $helper->ask($input, $output, $question);
        }

        $user = new User;
        $user->setUsername($username)
            ->setSlug($this->slugger->slug($user->getUsername()))
            ->setPassword($this->hasher->hashPassword($user, $password))
            ->setEmail($email)
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
            ->setCreatedAt(new \DateTimeImmutable);

        $this->manager->persist($user);
        $this->manager->flush();

        $io->success('A new admin user has been created ! 🚀');

        return Command::SUCCESS;
    }
}
