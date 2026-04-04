package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.OrderStatusHistoryDto;
import com.sieuthithucung.entity.OrderStatusHistoryEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.OrderStatusHistoryMapper;
import com.sieuthithucung.repository.OrderStatusHistoryRepository;
import com.sieuthithucung.service.OrderStatusHistoryService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class OrderStatusHistoryServiceImpl implements OrderStatusHistoryService {

    private final OrderStatusHistoryRepository repository;

    public OrderStatusHistoryServiceImpl(OrderStatusHistoryRepository repository) {
        this.repository = repository;
    }
    @Override
    public OrderStatusHistoryDto create(OrderStatusHistoryDto dto) {
        return createInternal(dto);
    }

    @Override
    public OrderStatusHistoryDto update(Long id, OrderStatusHistoryDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Order status history not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public OrderStatusHistoryDto findById(Long id) {
        return findByIdInternal(id);
    }

    @Override
    public List<OrderStatusHistoryDto> getAll() {
        return getAllInternal();
    }

    private OrderStatusHistoryDto createInternal(OrderStatusHistoryDto dto) {
        OrderStatusHistoryEntity entity = OrderStatusHistoryMapper.mapToOrderStatusHistoryEntity(dto);
        OrderStatusHistoryEntity saved = repository.save(entity);
        return OrderStatusHistoryMapper.mapToOrderStatusHistoryDto(saved);
    }

    private OrderStatusHistoryDto updateInternal(Long id, OrderStatusHistoryDto dto) {
        OrderStatusHistoryEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Order status history not found with id: " + id));

        OrderStatusHistoryEntity source = OrderStatusHistoryMapper.mapToOrderStatusHistoryEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        OrderStatusHistoryEntity saved = repository.save(existing);
        return OrderStatusHistoryMapper.mapToOrderStatusHistoryDto(saved);
    }

    private OrderStatusHistoryDto findByIdInternal(Long id) {
        OrderStatusHistoryEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Order status history not found with id: " + id));
        return OrderStatusHistoryMapper.mapToOrderStatusHistoryDto(entity);
    }

    private List<OrderStatusHistoryDto> getAllInternal() {
        return repository.findAll().stream().map(OrderStatusHistoryMapper::mapToOrderStatusHistoryDto).toList();
    }

    private String[] getNullPropertyNames(Object source) {
        BeanWrapper src = new BeanWrapperImpl(source);
        PropertyDescriptor[] pds = src.getPropertyDescriptors();

        Set<String> emptyNames = new HashSet<>();
        for (PropertyDescriptor pd : pds) {
            Object srcValue = src.getPropertyValue(pd.getName());
            if (srcValue == null) {
                emptyNames.add(pd.getName());
            }
        }
        return emptyNames.toArray(new String[0]);
    }
}