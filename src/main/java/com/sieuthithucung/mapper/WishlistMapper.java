package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.WishlistDto;
import com.sieuthithucung.entity.WishlistEntity;
import org.springframework.stereotype.Component;
import tools.jackson.databind.ObjectMapper;

@Component
public class WishlistMapper extends BaseMapper<WishlistEntity, WishlistDto> {

    public WishlistMapper(ObjectMapper objectMapper) {
        super(objectMapper, WishlistEntity.class, WishlistDto.class);
    }
}

