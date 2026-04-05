<?php

use Emaia\LaravelHotwireTurbo\Models\Name;
use Emaia\LaravelHotwireTurbo\Views\RecordIdentifier;
use Illuminate\Database\Eloquent\Model;

// Fake model for testing
class Message extends Model
{
    protected $guarded = [];
}

class ArticleComment extends Model
{
    protected $guarded = [];
}

it('generates dom_id for existing model', function () {
    $model = new Message;
    $model->id = 15;

    expect(dom_id($model))->toBe('message_15');
});

it('generates dom_id with prefix', function () {
    $model = new Message;
    $model->id = 15;

    expect(dom_id($model, 'edit'))->toBe('edit_message_15');
});

it('generates dom_id for new model without key', function () {
    $model = new Message;

    expect(dom_id($model))->toBe('create_message');
});

it('generates dom_id for new model with custom prefix', function () {
    $model = new Message;

    expect(dom_id($model, 'new'))->toBe('new_message');
});

it('generates dom_class', function () {
    $model = new Message;

    expect(dom_class($model))->toBe('message');
});

it('generates dom_class with prefix', function () {
    $model = new Message;

    expect(dom_class($model, 'edit'))->toBe('edit_message');
});

it('handles multi-word model names', function () {
    $model = new ArticleComment;
    $model->id = 5;

    expect(dom_id($model))->toBe('article_comment_5');
    expect(dom_class($model))->toBe('article_comment');
});

it('throws exception for objects without getKey', function () {
    $obj = new stdClass;

    new RecordIdentifier($obj);
})->throws(InvalidArgumentException::class, 'does not have a getKey() method');

it('resolves name singular and plural', function () {
    $model = new Message;
    $name = Name::forModel($model);

    expect($name->singular)->toBe('message');
    expect($name->plural)->toBe('messages');
    expect($name->element)->toBe('message');
});

it('caches name resolution', function () {
    $model1 = new Message;
    $model2 = new Message;

    $name1 = Name::forModel($model1);
    $name2 = Name::forModel($model2);

    expect($name1)->toBe($name2);
});
