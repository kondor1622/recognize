<?php

namespace OCA\Recognize\Command;

use OCA\Recognize\Service\ClassifyImagesService;
use OCA\Recognize\Service\Logger;
use OCP\IUser;
use OCP\IUserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClassifyImages extends Command {
	/**
	 * @var IUserManager
	 */
	private $userManager;
	/**
	 * @var \OCA\Recognize\Service\ClassifyImagesService
	 */
	private $imageClassifier;

	/**
	 * @var \OCA\Recognize\Service\Logger
	 */
	private $logger;


	public function __construct(IUserManager $userManager, ClassifyImagesService $imageClassifier, Logger $logger) {
		parent::__construct();
		$this->userManager = $userManager;
		$this->imageClassifier = $imageClassifier;
		$this->logger = $logger;
	}

	/**
	 * Configure the command
	 *
	 * @return void
	 */
	protected function configure() {
		$this->setName('recognize:classify-images')
			->setDescription('Classify all photos in this installation');
	}

	/**
	 * Execute the command
	 *
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		try {
			$users = [];
			$this->userManager->callForSeenUsers(function (IUser $user) use (&$users) {
				$users[] = $user->getUID();
			});
			$this->logger->setCliOutput($output);
			foreach ($users as $user) {
				$this->imageClassifier->run($user);
			}
		} catch (\Exception $ex) {
			$output->writeln('<error>Failed to classify images</error>');
			$output->writeln($ex->getMessage());
			return 1;
		}

		return 0;
	}
}
