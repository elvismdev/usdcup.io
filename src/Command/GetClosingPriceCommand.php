<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\PriceHistory;
use App\Service\RevolicoService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use App\Util\UtilityBox;

class GetClosingPriceCommand extends Command
{

    /**
     * @var ParameterBagInterface
     */
    protected $params;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    protected static $defaultName = 'app:get-closing-price';
    protected static $defaultDescription = 'Gets a closing price and records into the Price History entity.';

    public function __construct(ParameterBagInterface $params, EntityManagerInterface $em)
    {
        $this->params = $params;
        $this->em     = $em;
        parent::__construct();
    }

    protected function configure(): void
    {
        // $this
        //     ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
        //     ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        // ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Get last price logged.
        $priceHistoryRepository = $this->em->getRepository(PriceHistory::class);
        $lastPrice = $priceHistoryRepository->findLastPriceInserted();
        $lastAveragePrice = $lastPrice->getClosingPrice();

        // Calculate a max price value.
        $calcMaxPrice = UtilityBox::generateMaxPrice($lastAveragePrice);

        // Initialize platform service.
        $revolicoService = new RevolicoService(
            $this->getParameter('banned_words'),
            $this->getParameter('search_text'),
            $this->getParameter('min_price'),
            $calcMaxPrice,
            $this->getParameter('ad_platform_graphql_endpoint'),
            $this->getParameter('user_agent')
        );

        // Get a reponse from platform.
        $response = $revolicoService->getAds();

        // Check status of request.
        if (isset($response['errors']) && !empty($response['errors'])) {
            throw new \Exception(sprintf('Remote platform API Error: "%s".', $response['errors']));
        }

        // Find the average price.
        $averagePriceResults = $revolicoService->findAveragePrice();

        if (isset($averagePriceResults['pricesQty']) && isset($averagePriceResults['averagePrice'])) {
            // Find min and max ad prices.
            $minMaxPriceAds = $revolicoService->findMinMaxPriceAds();

            $priceHistory = new PriceHistory();
            $priceHistory->setCurrency('USD');
            $priceHistory->setUnixCreatedAt(round(microtime(true) * 1000));
            $priceHistory->setClosingPrice(round($averagePriceResults['averagePrice'], 2));
            $priceHistory->setAdsPricesEval($averagePriceResults['pricesQty']);
            $priceHistory->setMaxPriceAd($minMaxPriceAds['maxPriceAd']);
            $priceHistory->setMinPriceAd($minMaxPriceAds['minPriceAd']);
            $priceHistory->setMaxPriceAdUrl($minMaxPriceAds['maxPriceAdUrl']);
            $priceHistory->setMinPriceAdUrl($minMaxPriceAds['minPriceAdUrl']);

            // Tell doctrine we want to save priceHistory.
            $this->em->persist($priceHistory);

            $this->em->flush();

            $io->success(
                sprintf(
                    'Logged in price history table an average price of "%s" calculated from "%s" filtered ads.',
                    $averagePriceResults['averagePrice'],
                    $averagePriceResults['pricesQty']
                )
            );

            // Tweet price update.
            $dailyTweetCommand = $this->getApplication()->find('app:tweet-daily-price-change');
            $returnCode = $dailyTweetCommand->run(new ArrayInput([]), $output);
        } else {
            $io->success('No average price found to record on the price history table.');
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
