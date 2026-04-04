package com.sieuthithucung.mapper;

import tools.jackson.databind.ObjectMapper;
import com.sieuthithucung.dto.OrderDto;
import com.sieuthithucung.entity.OrderEntity;
import org.springframework.stereotype.Component;

@Component
public class OrderMapper extends BaseMapper<OrderEntity, OrderDto> {

    public OrderMapper(ObjectMapper objectMapper) {
        super(objectMapper, OrderEntity.class, OrderDto.class);
    }
}

