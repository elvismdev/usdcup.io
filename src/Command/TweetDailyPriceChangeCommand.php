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

class TweetDailyPriceChangeCommand extends Command
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

    protected static $defaultName = 'app:tweet-daily-price-change';
    protected static $defaultDescription = 'Tweets daily a price update to the app Twitter profile feed after the app gets the latest closing price for the day.';

    public function __construct(ParameterBagInterface $params, EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->params     = $params;
        $this->em         = $em;
        $this->translator = $translator;
        parent::__construct();
    }

    protected function configure(): void
    {
        // $this
        //     ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
        //     ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        // ;
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

        // Get last week and current price logged.
        $priceHistoryRepository = $this->em->getRepository(PriceHistory::class);
        $todayPriceHistory = $priceHistoryRepository->findLastPriceInserted();
        $yesterdayPrice = $priceHistoryRepository->findYesterdayPrice($todayPriceHistory->getCreatedAt());

        // Calculate and post to twitter if we have both dates to compare.
        if ($yesterdayPrice && $todayPriceHistory) {
            // Set tweet variables.
            $upPointTriangle = "🔺";
            $downPointTriangle = "🔻";
            $pointTriangle = "";

            // Get closing prices.
            $yesterdayPrice = $yesterdayPrice->getClosingPrice();
            $todayPrice = $todayPriceHistory->getClosingPrice();

            // Calculate price change difference.
            $amountChange = $yesterdayPrice - $todayPrice;

            // Calculate percentage change difference.
            $percentChange = ($amountChange / $yesterdayPrice) * 100;

            // Set tweet triangle icon if the value is an increase or decrease from previous week.
            if ($amountChange < 0) {
                $pointTriangle = $upPointTriangle;
            } else {
                $pointTriangle = $downPointTriangle;
            }

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
                    'tweet_daily_text',
                    [
                    '%pointTriangle%' => $pointTriangle,
                    '%amountChange%' => abs($amountChange),
                    '%percentChange%' => round(abs($percentChange), 2),
                    '%todayPrice%' => $todayPrice,
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
