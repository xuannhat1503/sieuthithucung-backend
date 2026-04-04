package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.CartItemDto;
import com.sieuthithucung.entity.CartItemEntity;
import com.sieuthithucung.mapper.CartItemMapper;
import com.sieuthithucung.repository.CartItemRepository;
import org.springframework.stereotype.Service;

@Service
public class CartItemService extends AbstractCrudService<CartItemEntity, Long, CartItemDto> {

    public CartItemService(CartItemRepository repository, CartItemMapper mapper) {
        super(repository, mapper, "Cart item");
    }
}

