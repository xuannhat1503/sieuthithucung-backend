package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.OrderStatusHistoryDto;
import com.sieuthithucung.entity.OrderStatusHistoryEntity;
import com.sieuthithucung.mapper.OrderStatusHistoryMapper;
import com.sieuthithucung.repository.OrderStatusHistoryRepository;
import org.springframework.stereotype.Service;

@Service
public class OrderStatusHistoryService extends AbstractCrudService<OrderStatusHistoryEntity, Long, OrderStatusHistoryDto> {

    public OrderStatusHistoryService(OrderStatusHistoryRepository repository, OrderStatusHistoryMapper mapper) {
        super(repository, mapper, "Order status history");
    }
}

