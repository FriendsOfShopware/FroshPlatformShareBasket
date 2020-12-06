<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Command;

use Frosh\ShareBasket\Core\Content\ShareBasket\ShareBasketDefinition;
use Frosh\ShareBasket\Services\ShareBasketServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShareBasketCleanupCommand extends Command
{
    protected static $defaultName = 'frosh:share-basket-cleanup';

    /**
     * @var ShareBasketServiceInterface
     */
    private $shareBasketService;

    public function __construct(ShareBasketServiceInterface $shareBasketService)
    {
        parent::__construct();

        $this->shareBasketService = $shareBasketService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $event = $this->shareBasketService->cleanup();

        if ($event === null) {
            return 0;
        }

        $result = $event->getEventByEntityName(ShareBasketDefinition::ENTITY_NAME);

        if ($result !== null) {
            $deleted = count($result->getIds());
            $output->writeln($deleted . ' deleted');
        }

        return 0;
    }
}
