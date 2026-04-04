package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.OrderDto;
import com.sieuthithucung.entity.OrderEntity;
import com.sieuthithucung.mapper.OrderMapper;
import com.sieuthithucung.repository.OrderRepository;
import org.springframework.stereotype.Service;

@Service
public class OrderService extends AbstractCrudService<OrderEntity, Long, OrderDto> {

    public OrderService(OrderRepository repository, OrderMapper mapper) {
        super(repository, mapper, "Order");
    }
}

