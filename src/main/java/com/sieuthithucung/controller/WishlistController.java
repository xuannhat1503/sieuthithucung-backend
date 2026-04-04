package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.WishlistDto;
import com.sieuthithucung.service.WishlistService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/wishlists")
public class WishlistController extends AbstractCrudController<Long, WishlistDto> {

    public WishlistController(WishlistService service) {
        super(service);
    }
}

