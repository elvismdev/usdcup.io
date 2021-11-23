<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Abraham\TwitterOAuth\TwitterOAuth;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TweetPriceChangeCommand extends Command
{

    /**
     * @var ParameterBagInterface
     */
    protected $params;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    protected static $defaultName = 'app:tweet-price-change';
    protected static $defaultDescription = 'Add a short description for your command';

    public function __construct(ParameterBagInterface $params, EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->params     = $params;
        $this->em         = $em;
        $this->translator = $translator;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    /**
     * Posts a Tweet.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @link https://twitteroauth.com/
     *
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        // Set twitter emojis.
        $upPointTriangle = "🔺";
        $downPointTriangle = "🔻";

        // $arg1 = $input->getArgument('arg1');

        // if ($arg1) {
        //     $io->note(sprintf('You passed an argument: %s', $arg1));
        // }

        // if ($input->getOption('option1')) {
        //     // ...
        // }

        // Initialize Twitter API client.
        $connection = new TwitterOAuth(
            $this->getParameter('twitter_api_key'),
            $this->getParameter('twitter_api_key_secret'),
            $this->getParameter('twitter_access_token'),
            $this->getParameter('twitter_access_token_secret')
        );
        $connection->setApiVersion(2);

        // $content = $connection->get("account/verify_credentials");

        // $statues = $connection->post("statuses/update", ["status" => "hello world"]);

        // $statuses = $connection->get("statuses/home_timeline", ["count" => 25, "exclude_replies" => true]);

        $response = $connection->post(
            'tweets',
            ["text" => $this->translator->trans('tweet_text', ['%pointTriangle%' => $upPointTriangle])],
            true
        );

        // var_dump($response);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }

    /**
     * Get parameter from ParameterBag
     *
     * @param string $name
     *
     * @return mixed
     */
    private function getParameter($name)
    {
        return $this->params->get($name);
    }
}
