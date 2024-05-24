<?php

namespace App\Twig;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use App\Repository\ConfigurationRepository;
use App\Repository\ModificationsRepository;

class AppExtension extends AbstractExtension
{
    private $modificationsRepository;

    public function __construct(ConfigurationRepository $modificationsRepository)
    {
        $this->modificationsRepository = $modificationsRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('execute_modifications', [$this, 'executeModifications'], ['is_safe' => ['html']]),
        ];
    }

    public function executeModifications(): string
    {
        $modification = $this->modificationsRepository->findLatest();
        return $modification ? $modification->getCode() : '';
    }
}
