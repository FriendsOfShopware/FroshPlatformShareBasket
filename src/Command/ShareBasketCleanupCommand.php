<?php
declare(strict_types=1);

namespace Frosh\ShareBasket\Command;

use Frosh\ShareBasket\Core\Content\ShareBasket\ShareBasketDefinition;
use Frosh\ShareBasket\Services\ShareBasketServiceInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('frosh:share-basket-cleanup')]
class ShareBasketCleanupCommand extends Command
{
    public function __construct(private readonly ShareBasketServiceInterface $shareBasketService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $event = $this->shareBasketService->cleanup();

        if (!$event instanceof EntityWrittenContainerEvent) {
            return 0;
        }

        $result = $event->getEventByEntityName(ShareBasketDefinition::ENTITY_NAME);

        if ($result !== null) {
            $deleted = \count($result->getIds());
            $output->writeln($deleted . ' deleted');
        }

        return 0;
    }
}
