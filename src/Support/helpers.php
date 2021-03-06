<?php

use Illuminate\Support\Facades\Blade;
use Symfony\Component\Debug\Exception\FatalThrowableError;

function render($string, $__data = [])
{
    $__data['__env'] = app(\Illuminate\View\Factory::class);
    $__php = Blade::compileString($string);

    $obLevel = ob_get_level();
    ob_start();
    extract($__data, EXTR_SKIP);

    try {
        eval('?' . '>' . $__php);
    } catch (Exception $e) {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }

        throw $e;
    } catch (Throwable $e) {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }

        throw new FatalThrowableError($e);
    }

    return ob_get_clean();
}

function move()
{
    return new \Uteq\Move\Facades\Move();
}

function move_class_to_label($class)
{
    return Str::plural(Str::title(Str::snake(class_basename($class), ' ')));
}
