package com.sieuthithucung.mapper;

import tools.jackson.databind.ObjectMapper;
import com.sieuthithucung.dto.OrderItemDto;
import com.sieuthithucung.entity.OrderItemEntity;
import org.springframework.stereotype.Component;

@Component
public class OrderItemMapper extends BaseMapper<OrderItemEntity, OrderItemDto> {

    public OrderItemMapper(ObjectMapper objectMapper) {
        super(objectMapper, OrderItemEntity.class, OrderItemDto.class);
    }
}

