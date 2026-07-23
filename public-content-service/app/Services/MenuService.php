<?php

namespace App\Services;

use App\Models\Menu;

class MenuService
{
    public function index($request)
    {
        $query = Menu::active();

        if ($request->filled('is_show_front')) {
            $query->where('is_show_front', $request->is_show_front);
        }

        $query->orderBy('order', 'asc')
            ->with([
                'subMenu.childSubMenu.content',
                'content',
            ]);

        $menus = $query->get();

        $data = [];
        foreach ($menus as $menu) {
            if (($menu->has_sub && count($menu->subMenu) > 0) || ! $menu->has_sub) {
                $row = [
                    'title' => $menu->title,
                    'slug' => $menu->slug,
                    'has_sub' => $menu->has_sub,
                    'is_show_front' => $menu->is_show_front,
                ];
                if ($menu->has_sub) {
                    foreach ($menu->subMenu as $sub) {
                        if (($sub->has_child && count($sub->childSubMenu) > 0) || ! $sub->has_child) {
                            $rowSub = [
                                'title' => $sub->title,
                                'slug' => $sub->slug,
                                'has_child' => $sub->has_child,
                            ];
                            if ($sub->has_child) {
                                foreach ($sub->childSubMenu as $child) {
                                    if ($child->content) {
                                        $rowChild = [
                                            'title' => $child->title,
                                            'slug' => $child->slug,
                                            'content' => [
                                                'type' => $child->content->type,
                                                'layout' => $child->content->layout,
                                            ],
                                        ];
                                        $rowSub['child_sub_menu'][] = $rowChild;
                                    }
                                }
                            } else {
                                if ($sub->content) {
                                    $rowSub['content'] = [
                                        'type' => $sub->content->type,
                                        'layout' => $sub->content->layout,
                                    ];
                                }
                            }
                            $row['sub_menu'][] = $rowSub;
                        }
                    }
                } else {
                    if ($menu->content) {
                        $row['content'] = [
                            'type' => $menu->content->type,
                            'layout' => $menu->content->layout,
                        ];
                    }
                }
                $data[] = $row;
            }
        }

        return $data;
    }
}
