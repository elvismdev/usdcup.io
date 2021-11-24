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
use App\Entity\PriceHistory;

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
        // Set tweet variables.
        $upPointTriangle = "🔺";
        $downPointTriangle = "🔻";
        $pointTriangle = "";

        // Get last week and current price logged.
        $priceHistoryRepository = $this->em->getRepository(PriceHistory::class);
        $lastWeekPriceHistory = $priceHistoryRepository->findLastWeekPrice();
        $todayPriceHistory = $priceHistoryRepository->findLastPriceInserted();

        // Calculate and post to twitter if we have both dates to compare.
        if ($lastWeekPriceHistory && $todayPriceHistory) {
            // Get dates.
            $lastWeekDate = $lastWeekPriceHistory->getCreatedAt()->format('d/m/Y');
            $todayDate = $todayPriceHistory->getCreatedAt()->format('d/m/Y');

            // Get closing prices.
            $lastWeekPrice = $lastWeekPriceHistory->getClosingPrice();
            $todayPrice = $todayPriceHistory->getClosingPrice();

            // Calculate price change difference.
            $amountChange = $lastWeekPrice - $todayPrice;

            // Calculate percentage change difference.
            $percentChange = ($amountChange / $lastWeekPrice) * 100;

            // Set tweet triangle icon if the value is an increase or decrease from previous week.
            if ($amountChange < 0) {
                $pointTriangle = $upPointTriangle;
            } else {
                $pointTriangle = $downPointTriangle;
            }

            // print_r($lastWeekDate);

            // Initialize Twitter API client.
            $connection = new TwitterOAuth(
                $this->getParameter('twitter_api_key'),
                $this->getParameter('twitter_api_key_secret'),
                $this->getParameter('twitter_access_token'),
                $this->getParameter('twitter_access_token_secret')
            );
            $connection->setApiVersion(2);

            // Send the Tweet.
            $response = $connection->post(
                'tweets',
                ["text" => $this->translator->trans(
                    'tweet_text',
                    [
                    '%pointTriangle%' => $pointTriangle,
                    '%amountChange%' => abs($amountChange),
                    '%percentChange%' => round(abs($percentChange), 2),
                    '%todayDate%' => $todayDate,
                    '%lastWeekDate%' => $lastWeekDate,
                    '%todayPrice%' => $todayPrice,
                    '%lastWeekPrice%' => $lastWeekPrice,
                    ]
                ),
                ],
                true
            );

            // If tweet was published, print a success message. Otherwise print a notice error.
            if (isset($response->data->id) && !empty($response->data->id)) {
                $io->success('Tweet posted! '.$this->getParameter('twitter_profile_link').'/status/'.$response->data->id);
            } else {
                $io->note('No tweet posted. Maybe an API error?');
            }
        } else {
            $io->note('No tweet posted. Missing dates to calculate difference.');
        }

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
