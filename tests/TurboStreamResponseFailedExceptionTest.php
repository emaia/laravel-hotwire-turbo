<?php

use Emaia\LaravelHotwireTurbo\Enums\Action;
use Emaia\LaravelHotwireTurbo\Exceptions\TurboStreamResponseFailedException;
use Emaia\LaravelHotwireTurbo\Stream;
use Emaia\LaravelHotwireTurbo\StreamCollection;

describe('class hierarchy and back-compat', function () {
    it('extends InvalidArgumentException', function () {
        $exception = TurboStreamResponseFailedException::missingTarget();

        expect($exception)
            ->toBeInstanceOf(TurboStreamResponseFailedException::class)
            ->toBeInstanceOf(InvalidArgumentException::class)
            ->toBeInstanceOf(LogicException::class);
    });
});

describe('named constructors', function () {
    it('missingTarget() carries the expected message', function () {
        $exception = TurboStreamResponseFailedException::missingTarget();

        expect($exception->getMessage())->toBe('Either target or targets must be provided');
    });

    it('builderEmpty() interpolates the caller method name', function () {
        $exception = TurboStreamResponseFailedException::builderEmpty('view');

        expect($exception->getMessage())
            ->toBe('Cannot call view() on TurboStreamBuilder before adding a stream.');
    });

    it('nonStreamItem() carries the expected message', function () {
        $exception = TurboStreamResponseFailedException::nonStreamItem();

        expect($exception->getMessage())->toBe('Collection items must be instances of Stream');
    });
});

describe('integration: thrown from stream APIs', function () {
    it('Stream constructor throws the typed exception when target is missing', function () {
        try {
            new Stream(Action::UPDATE, '', '');
            $this->fail('Expected exception was not thrown.');
        } catch (TurboStreamResponseFailedException $exception) {
            expect($exception->getMessage())->toBe('Either target or targets must be provided');
        }
    });

    it('TurboStreamBuilder::view() throws the typed exception when no stream was added', function () {
        try {
            turbo_stream()->view('whatever');
            $this->fail('Expected exception was not thrown.');
        } catch (TurboStreamResponseFailedException $exception) {
            expect($exception->getMessage())
                ->toBe('Cannot call view() on TurboStreamBuilder before adding a stream.');
        }
    });

    it('TurboStreamBuilder::escape() throws the typed exception when no stream was added', function () {
        try {
            turbo_stream()->escape();
            $this->fail('Expected exception was not thrown.');
        } catch (TurboStreamResponseFailedException $exception) {
            expect($exception->getMessage())
                ->toBe('Cannot call escape() on TurboStreamBuilder before adding a stream.');
        }
    });

    it('StreamCollection constructor throws the typed exception for non-stream items', function () {
        try {
            new StreamCollection(['not-a-stream']);
            $this->fail('Expected exception was not thrown.');
        } catch (TurboStreamResponseFailedException $exception) {
            expect($exception->getMessage())->toBe('Collection items must be instances of Stream');
        }
    });

    it('StreamCollection::add() throws the typed exception for non-stream items', function () {
        try {
            StreamCollection::make()->add('not-a-stream');
            $this->fail('Expected exception was not thrown.');
        } catch (TurboStreamResponseFailedException $exception) {
            expect($exception->getMessage())->toBe('Collection items must be instances of Stream');
        }
    });
});
