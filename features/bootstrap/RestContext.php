<?php

/*
 * This file is part of the Silverback API Components Bundle Project
 *
 * (c) Daniel West <daniel@silverback.is>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Silverback\ApiComponentsBundle\Features\Bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\MinkContext;
use Behatch\Context\RestContext as BaseRestContext;
use Behatch\Context\RestContext as BehatchRestContext;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;

/**
 * @author Daniel West <daniel@silverback.is>
 */
class RestContext implements Context
{
    private ?BaseRestContext $restContext;
    private ?MinkContext $minkContext;
    public array $components = [];
    public string $now = '';
    private ?BehatchRestContext $behatchRestContext;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $this->restContext = $scope->getEnvironment()->getContext(BaseRestContext::class);
        $this->minkContext = $scope->getEnvironment()->getContext(MinkContext::class);
        $this->behatchRestContext = $scope->getEnvironment()->getContext(BehatchRestContext::class);
    }

    /**
     * @AfterScenario
     */
    public function resetNow(): void
    {
        $this->now = '';
    }

    /**
     * @Transform /^(now)$/
     * @BeforeScenario @saveNow
     */
    public function getCachedNow(): string
    {
        if ($this->now) {
            return $this->now;
        }

        return $this->now = date('Y-m-d\TH:i:s+00:00');
    }

    /**
     * @Transform /^base64(.*)$/
     */
    public function castBase64FileToString(string $value)
    {
        $filePath = rtrim($this->behatchRestContext->getMinkParameter('files_path'), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR . $value;
        $normalizer = new DataUriNormalizer();

        return $normalizer->normalize(new \SplFileObject($filePath));
    }

    /**
     * @Transform /^base64string(.*)$/
     */
    public function castBase64FileToSimpleString(string $value)
    {
        $filePath = rtrim($this->behatchRestContext->getMinkParameter('files_path'), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR . $value;

        return base64_encode(file_get_contents($filePath));
    }

    /**
     * @Given I send a :method request to :url with data:
     */
    public function iSendARequestToWithData($method, $url, TableNode $tableNode)
    {
        $this->restContext->iSendARequestToWithBody($method, $url, new PyStringNode([json_encode($this->castTableNodeToArray($tableNode))], 0));
    }

    /**
     * @When /^I send a "([^"]*)" request to the component "([^"]*)"(?:(?: and the postfix "([^"]*)"|)?(?: with body:|)|)$/i
     */
    public function iSendARequestToTheComponentWithBody(string $method, string $component, ?string $postfix = null, ?PyStringNode $body = null)
    {
        if (!isset($this->components[$component])) {
            throw new ExpectationException(sprintf("The component with name $component has not been defined. (Components that exist are `%s`)", implode('`, `', array_keys($this->components))), $this->minkContext->getSession()->getDriver());
        }
        $endpoint = $this->components[$component] . ($postfix ?: '');

        return $this->restContext->iSendARequestToWithBody($method, $endpoint, $body ?? new PyStringNode([], 0));
    }

    /**
     * @When /^I send a "([^"]*)" request to the component "([^"]*)"(?: and the postfix "([^"]*)"|)? with data:$/i
     */
    public function iSendARequestToTheComponentWithData(string $method, string $component, TableNode $tableNode, ?string $postfix = null)
    {
        $data = $this->castTableNodeToArray($tableNode);

        return $this->iSendARequestToTheComponentWithBody($method, $component, $postfix, new PyStringNode([json_encode($data)], 0));
    }

    /**
     * @Then the file :file should exist
     */
    private function castTableNodeToArray(TableNode $tableNode): array
    {
        $data = array_map(function ($value) {
            if ('null' === $value) {
                $value = null;
            }

            if ('now' === $value) {
                $value = $this->getCachedNow();
            }

            if ('invalid_draft' === $value) {
                $value = ['name' => ''];
            }

            if ('valid_draft' === $value) {
                $value = ['name' => 'John Doe'];
            }

            if ('valid_published' === $value) {
                $value = ['name' => 'John Doe', 'description' => 'nobody'];
            }

            if (\is_string($value) && preg_match('/^base64\((.*)\)$/', $value, $matches)) {
                $value = $this->castBase64FileToString($matches[1]);
            }

            if (\is_string($value) && preg_match('/^base64string\((.*)\)$/', $value, $matches)) {
                $value = $this->castBase64FileToSimpleString($matches[1]);
            }

            return $value;
        }, array_combine($tableNode->getRow(0), $tableNode->getRow(1)));

        if (isset($data['resourceData']) && \is_array($resourceData = $data['resourceData'])) {
            unset($data['resourceData']);
            $data = array_merge($data, $resourceData);
        }

        return $data;
    }
}