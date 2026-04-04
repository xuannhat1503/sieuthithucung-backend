package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.OrderItemDto;
import com.sieuthithucung.entity.OrderItemEntity;
import com.sieuthithucung.mapper.OrderItemMapper;
import com.sieuthithucung.repository.OrderItemRepository;
import org.springframework.stereotype.Service;

@Service
public class OrderItemService extends AbstractCrudService<OrderItemEntity, Long, OrderItemDto> {

    public OrderItemService(OrderItemRepository repository, OrderItemMapper mapper) {
        super(repository, mapper, "Order item");
    }
}

