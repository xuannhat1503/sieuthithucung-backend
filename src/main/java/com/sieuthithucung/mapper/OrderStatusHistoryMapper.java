package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.OrderStatusHistoryDto;
import com.sieuthithucung.entity.OrderStatusHistoryEntity;
import org.springframework.stereotype.Component;
import tools.jackson.databind.ObjectMapper;

@Component
public class OrderStatusHistoryMapper extends BaseMapper<OrderStatusHistoryEntity, OrderStatusHistoryDto> {

    public OrderStatusHistoryMapper(ObjectMapper objectMapper) {
        super(objectMapper, OrderStatusHistoryEntity.class, OrderStatusHistoryDto.class);
    }
}

