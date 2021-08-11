<?php

namespace App\Controller;

use App\Repository\ItemRepository;

class ItemController extends AbstractController
{
    private ItemRepository $itemRepository;

    public function __construct()
    {
        parent::__construct();
        $this->itemRepository = new ItemRepository();
    }

    /**
     * List items
     */
    public function index(): string
    {
        $items = $this->itemRepository->selectAll('title');

        return $this->twig->render('Item/index.html.twig', ['items' => $items]);
    }

    /**
     * Show informations for a specific item
     */
    public function show(int $id): string
    {
        $item = $this->itemRepository->selectOneById($id);

        return $this->twig->render('Item/show.html.twig', ['item' => $item]);
    }

    /**
     * Edit a specific item
     */
    public function edit(int $id): string
    {
        $item = $this->itemRepository->selectOneById($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // clean $_POST data
            $item = array_map('trim', $_POST);

            // TODO validations (length, format...)

            // if validation is ok, update and redirection
            $this->itemRepository->update($item);
            header('Location: /item/show/' . $id);
        }

        return $this->twig->render('Item/edit.html.twig', [
            'item' => $item,
        ]);
    }

    /**
     * Add a new item
     */
    public function add(): string
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // clean $_POST data
            $item = array_map('trim', $_POST);

            // TODO validations (length, format...)

            // if validation is ok, insert and redirection
            $id = $this->itemRepository->insert($item);
            header('Location:/item/show/' . $id);
        }

        return $this->twig->render('Item/add.html.twig');
    }

    /**
     * Delete a specific item
     */
    public function delete(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->itemRepository->delete($id);
            header('Location:/item/index');
        }
    }
}
