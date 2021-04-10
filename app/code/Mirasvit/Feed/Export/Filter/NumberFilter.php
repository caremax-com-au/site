<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.1.30
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Feed\Export\Filter;

class NumberFilter
{

    /**
     * Addition
     *
     * @param int $input
     * @param int $operand
     * @return int
     */
    public static function plus($input, $operand)
    {
        return (float)$input + (float)$operand;
    }

    /**
     * Subtraction
     *
     * @param int $input
     * @param int $operand
     * @return int
     */
    public static function minus($input, $operand)
    {
        return (float)$input - (float)$operand;
    }

    /**
     * Multiplication
     *
     * @param int $input
     * @param int $operand
     * @return int
     */
    public static function times($input, $operand)
    {
        return (float)$input * (float)$operand;
    }

    /**
     * Division
     *
     * @param int $input
     * @param int $operand
     * @return int
     */
    public static function divided_by($input, $operand)
    {
        return (float)$input / (float)$operand;
    }

    /**
     * Modulo
     *
     * @param int $input
     * @param int $operand
     * @return int
     */
    public static function modulo($input, $operand)
    {
        return (int)$input % (int)$operand;
    }

    /**
     * Ceil
     *
     * Rounds an output up to the nearest integer.
     *
     * @param string $input
     * @return number
     */
    public function ceil($input)
    {
        return ceil((float)$input);
    }

    /**
     * Floor
     *
     * Rounds an output down to the nearest integer.
     *
     * @param string $input
     * @return number
     */
    public function floor($input)
    {
        return floor((float)$input);
    }

    /**
     * Round
     *
     * Rounds the output to the nearest integer or specified number of decimals.
     *
     * @param string $input
     * @param number $precision
     * @return number
     */
    public function round($input, $precision)
    {
        return round((float)$input, $precision);
    }

    /**
     * Number Format
     *
     * Format
     *
     * @param string $input
     * @param int    $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     * @return string
     */
    public function numberFormat($input, $decimals = 0, $decPoint = '.', $thousandsSep = ',')
    {
        return number_format((float)$input, $decimals, $decPoint, $thousandsSep);
    }

    /**
     * Price Format
     *
     * @param string $input
     * @return string
     */
    public function price($input)
    {
        $input = floatval($input);

        return number_format($input, 2, '.', '');
    }

    /**
     * Append
     *
     * Appends characters to a number.
     *
     * @param string $input
     * @param string $suffix
     * @return string
     */
    public function append($input, $suffix)
    {
        return $input ? $input . $suffix : $input;
    }
}
