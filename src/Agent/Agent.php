<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Agent;

interface Agent
{

	public const VERSION = '9.12.0.268';

	/**
	 * Accepts an array of distributed trace headers.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelicacceptdistributedtraceheaders
	 */
	public function acceptDistributedTraceHeaders(array $headers, string $transportType);

	/**
	 * Inserts W3C Trace Context headers and New Relic Distributed Tracing headers into an outbound array of headers.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelicinsertdistributedtraceheaders
	 */
	public function insertDistributedTraceHeaders(array $headers): bool;

	/**
	 * Attaches a custom attribute (key/value pair) to the current transaction and the current span (if enabled).
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_add_custom_parameter
	 *
	 * @param bool|float|int|string $value
	 */
	public function addCustomParameter(string $key, $value): bool;

	/**
	 * Attaches a custom attribute (key/value pair) to the current span.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelicaddcustomspanparameter-php-agent-api
	 *
	 * @param bool|float|int|string $value
	 */
	public function addCustomSpanParameter(string $key, $value): bool;

	/**
	 * Specify functions or methods for the agent to instrument with custom instrumentation.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_add_custom_tracer
	 */
	public function addCustomTracer(string $functionName): bool;

	/**
	 * Manually specify that a transaction is a background job or a web transaction.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_background_job
	 */
	public function backgroundJob(bool $flag = true): void;

	/**
	 * Enable or disable the capture of URL parameters.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_capture_params
	 */
	public function captureParams(bool $flag = true): void;

	/**
	 * Add a custom metric (in milliseconds) to time a component of your app not captured by default.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newreliccustommetric-php-agent-api
	 */
	public function customMetric(string $metricName, float $value): bool;

	/**
	 * Disable automatic injection of the browser monitoring snippet on particular pages.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_disable_autorum
	 */
	public function disableAutorum(): ?bool;

	/**
	 * Stop timing the current transaction, but continue instrumenting it.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_end_of_transaction
	 */
	public function endOfTransaction(): void;

	/**
	 * Stop instrumenting the current transaction immediately.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_end_transaction
	 */
	public function endTransaction(bool $ignore = false): bool;

	/**
	 * Returns a browser monitoring snippet to inject at the end of the HTML output.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_get_browser_timing_footer
	 */
	public function getBrowserTimingFooter(bool $includeTags = true): string;

	/**
	 * Returns a browser monitoring snippet to inject in the head of your HTML output.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_get_browser_timing_header
	 */
	public function getBrowserTimingHeader(bool $includeTags = true): string;

	/**
	 * Returns a collection of metadata necessary for linking data to a trace or an entity.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelicgetlinkingmetadata
	 */
	public function getLinkingMetadata(): array;

	/**
	 * Returns an associative array containing the identifiers of the current trace and the parent span.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelicgettracemetadata
	 */
	public function getTraceMetadata(): array;

	/**
	 * Ignore the current transaction when calculating Apdex.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_ignore_apdex
	 */
	public function ignoreApdex(): void;

	/**
	 * Do not instrument the current transaction.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_ignore_transaction
	 */
	public function ignoreTransaction(): void;

	/**
	 * Returns a value indicating whether or not the current transaction is marked as sampled.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelicissampled
	 */
	public function isSampled(): bool;

	/**
	 * Set custom name for current transaction.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_name_transaction
	 */
	public function nameTransaction(string $name): bool;

	/**
	 * Use these calls to collect errors that the PHP agent does not collect automatically and to set the callback
	 * for your own error and exception handler.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_notice_error
	 */
	public function noticeError(string $message, ?\Throwable $e = null): void;

	/**
	 * Use these calls to collect errors that the PHP agent does not collect automatically and to set the callback
	 * for your own error and exception handler.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_notice_error
	 */
	public function noticeFullError(int $errno, string $errstr, ?string $errfile = null, ?int $errline = null, ?string $errcontext = null): void;

	/**
	 * Record a custom event with the given name and attributes.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_record_custom_event
	 */
	public function recordCustomEvent(string $name, array $attributes): void;

	/**
	 * Records a datastore segment.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_record_datastore_segment
	 *
	 * @return mixed
	 */
	public function recordDatastoreSegment(callable $func, array $parameters);

	/**
	 * Sets the New Relic app name, which controls data rollup.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_set_appname
	 */
	public function setAppName(string $name, string $license = '', bool $xmit = false): bool;

	/**
	 * Create user-related custom attributes. newrelic_add_custom_parameter is more flexible.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_set_user_attributes
	 */
	public function setUserAttributes(string $userValue, string $accountValue, string $productValue): bool;

	/**
	 * Starts a new transaction, usually after manually ending a transaction.
	 *
	 * @link https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_start_transaction
	 */
	public function startTransaction(string $appName, string $license = ''): bool;

}
