<?php

namespace Phpactor\Extension\ExtensionManager\Rpc;

use Exception;
use Phpactor\Extension\ExtensionManager\Model\ExtensionState;
use Phpactor\Extension\ExtensionManager\Service\RemoverService;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\Response\CollectionResponse;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Response\ErrorResponse;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\MapResolver\Resolver;

class ExtensionRemoveHandler extends AbstractHandler implements Handler
{
    const PARAM_EXTENSION_NAME = 'extension_name';

    /**
     * @var RemoverService
     */
    private $remover;

    public function __construct(RemoverService $remover)
    {
        $this->remover = $remover;
    }

    public function name(): string
    {
        return 'extension_remove';
    }

    public function configure(Resolver $resolver)
    {
        $resolver->setDefaults([
            self::PARAM_EXTENSION_NAME => null,
        ]);
    }

    public function handle(array $arguments)
    {
        if (null === $arguments[self::PARAM_EXTENSION_NAME]) {
            $this->requireInput($this->createTextInput());
        }

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        try {
            $this->remover->removeExtension($arguments[self::PARAM_EXTENSION_NAME]);
        } catch (Exception $e) {
            return CollectionResponse::fromActions([
                ErrorResponse::fromMessageAndDetails(
                    'Failed to remove extension, try running `phpactor extension:remove` from the command line',
                    $e->getMessage()
                ),
                InputCallbackResponse::fromCallbackAndInputs(
                    Request::fromNameAndParameters(
                        $this->name(),
                        $arguments
                    ),
                    [
                        $this->createTextInput($arguments[self::PARAM_EXTENSION_NAME])
                    ]
                ),
            ]);
        };

        return EchoResponse::fromMessage(sprintf('Extension "%s" removed', $arguments[self::PARAM_EXTENSION_NAME]));
    }

    private function formatState(ExtensionState $extensionState)
    {
        if ($extensionState->isInstalled()) {
            return '✔';
        }
        return ' ';
    }

    private function createTextInput(string $default = '')
    {
        $textInput = TextInput::fromNameLabelAndDefault(
            self::PARAM_EXTENSION_NAME,
            'Extension name:',
            $default
        );
        return $textInput;
    }
}
