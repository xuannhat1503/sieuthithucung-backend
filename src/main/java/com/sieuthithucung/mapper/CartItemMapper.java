package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.CartItemDto;
import com.sieuthithucung.entity.CartItemEntity;
import org.springframework.stereotype.Component;
import tools.jackson.databind.ObjectMapper;

@Component
public class CartItemMapper extends BaseMapper<CartItemEntity, CartItemDto> {

    public CartItemMapper(ObjectMapper objectMapper) {
        super(objectMapper, CartItemEntity.class, CartItemDto.class);
    }
}

