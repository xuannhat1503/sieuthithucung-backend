package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.CartItemDto;
import com.sieuthithucung.service.CartItemService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/cart-items")
public class CartItemController extends AbstractCrudController<Long, CartItemDto> {

    public CartItemController(CartItemService service) {
        super(service);
    }
}

