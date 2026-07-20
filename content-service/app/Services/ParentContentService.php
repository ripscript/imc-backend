<?php

namespace App\Services;

use App\Models\ChildSubMenu;
use App\Models\Menu;
use App\Models\SubMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParentContentService
{
    public function getParentByType($type)
    {
        $query = DB::table('contents')
            ->leftJoin('menus', function ($join) {
                $join->on('menus.id', '=', 'contents.parent_id')
                    ->where('contents.parent_type', '=', Menu::class);
            })
            ->leftJoin('sub_menus', function ($join) {
                $join->on('sub_menus.id', '=', 'contents.parent_id')
                    ->where('contents.parent_type', '=', SubMenu::class)
                    ->join('menus as sub_menus_menus', 'sub_menus.menu_id', '=', 'sub_menus_menus.id');
            })
            ->leftJoin('child_sub_menus', function ($join) {
                $join->on('child_sub_menus.id', '=', 'contents.parent_id')
                    ->where('contents.parent_type', '=', ChildSubMenu::class)
                    ->join('sub_menus as child_sub_menus_sub_menus', 'child_sub_menus.sub_menu_id', '=', 'child_sub_menus_sub_menus.id')
                    ->join('menus as child_sub_menus_menus', 'child_sub_menus_sub_menus.menu_id', '=', 'child_sub_menus_menus.id');
            })
            ->select(
                'contents.id',
                'contents.parent_type',
                'contents.sub_type',
                DB::raw('COALESCE(menus.title, sub_menus_menus.title, child_sub_menus_menus.title) as menu_title'),
                DB::raw('COALESCE(sub_menus.title, child_sub_menus_sub_menus.title) as sub_menu_title'),
                'child_sub_menus.title as child_sub_menu_title',
                DB::raw('COALESCE(menus.id, sub_menus_menus.id, child_sub_menus_menus.id) as menu_id'),
                DB::raw('COALESCE(sub_menus.id, child_sub_menus_sub_menus.id) as sub_menu_id'),
                'child_sub_menus.id as child_sub_menu_id',
                DB::raw('COALESCE(menus.slug, sub_menus_menus.slug, child_sub_menus_menus.slug) as menu_slug'),
                DB::raw('COALESCE(sub_menus.slug, child_sub_menus_sub_menus.slug) as sub_menu_slug'),
                'child_sub_menus.slug as child_sub_menu_slug'
            )
            ->where('contents.type', '=', $type);

        $query->orderBy('contents.parent_type')
            ->orderBy('contents.parent_id');

        $getData = $query->get();

        $data = [];
        foreach ($getData as $item) {
            $data[$item->id] = [
                'id' => $item->id,
                'parent_type' => $item->parent_type,
                'sub_type' => $item->sub_type,
                'parent' => match ($item->parent_type) {
                    Menu::class => [
                        'id' => $item->menu_id,
                        'title' => $item->menu_title,
                        'slug' => $item->menu_slug,
                    ],
                    SubMenu::class => [
                        'id' => $item->sub_menu_id,
                        'title' => $item->sub_menu_title,
                        'slug' => $item->sub_menu_slug,
                        'menu' => [
                            'id' => $item->menu_id,
                            'title' => $item->menu_title,
                            'slug' => $item->menu_slug,
                        ],
                    ],
                    ChildSubMenu::class => [
                        'id' => $item->child_sub_menu_id,
                        'title' => $item->child_sub_menu_title,
                        'slug' => $item->child_sub_menu_slug,
                        'sub_menu' => [
                            'id' => $item->sub_menu_id,
                            'title' => $item->sub_menu_title,
                            'slug' => $item->sub_menu_slug,
                            'menu' => [
                                'id' => $item->menu_id,
                                'title' => $item->menu_title,
                                'slug' => $item->menu_slug,
                            ],
                        ],
                    ],
                },
            ];
        }

        return array_values($data);
    }
}