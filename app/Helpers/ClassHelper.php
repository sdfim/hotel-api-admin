<?php

namespace App\Helpers;

class ClassHelper
{
    public static function buttonClasses(): string
    {
        return 'btn text-mandarin-500 hover:text-white border-mandarin-500 hover:bg-mandarin-600 hover:border-mandarin-600
        focus:bg-mandarin-600 focus:text-white focus:border-mandarin-600 focus:ring focus:ring-mandarin-500/30
        active:bg-mandarin-600 active:border-mandarin-600 mr-1';
    }

    public static function sidebarPointClass(): string
    {
        return 'pl-6 pr-4 py-3 block text-sm font-medium text-gray-700 transition-all duration-150 ease-linear
        hover:text-mandarin-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white';
    }

    public static function sidebarParrentClass(): string
    {
        return 'nav-menu pl-6 pr-4 py-3 block text-sm font-medium text-gray-700 transition-all duration-150 ease-linear
        hover:text-mandarin-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white';
    }

    public static function sidebarCildrenBaseClass(): string
    {
        return 'pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear
        hover:text-mandarin-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white';
    }

    public static function sidebarCildrenClass(): string
    {
        return 'pl-14 ' . self::sidebarCildrenBaseClass();
    }

    public static function sidebarCildrenP2Class(): string
    {
        return 'nav-menu ' . self::sidebarCildrenClass();
    }

    public static function sidebarCildrenL2Class(): string
    {
        return 'pl-20 ' . self::sidebarCildrenBaseClass();
    }
}
